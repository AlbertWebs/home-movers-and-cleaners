<?php

namespace App\Http\Controllers;

use App\BookingSchedule;
use App\Models\Booking;
use App\Models\ContentPage;
use App\Models\Enquiry;
use App\Models\GalleryItem;
use App\Models\MediaAsset;
use App\Models\PricePackage;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\User;
use App\Notifications\BookingUpdate;
use App\Support\ImageOptimizer;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AdminController extends Controller
{
    public function login(): View
    {
        return view('admin.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt($credentials, $request->boolean('remember')) || ! Auth::user()->hasAdminRole('super_admin', 'administrator', 'booking_manager', 'content_editor')) {
            Auth::logout();

            return back()->withErrors(['email' => 'The supplied administrator details were not recognized.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        $user = Auth::user();
        $destination = match (true) {
            ! $user->is_admin && $user->role === 'booking_manager' => 'admin.bookings',
            ! $user->is_admin && $user->role === 'content_editor' => 'admin.homepage',
            default => 'admin.dashboard',
        };

        return redirect()->route($destination);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboard(Request $request): View
    {
        $filters = $request->validate(['range' => ['nullable', Rule::in(['30', '90', '180', '365'])]]);
        $range = (int) ($filters['range'] ?? 180);
        $today = CarbonImmutable::now('Africa/Nairobi')->startOfDay();
        $rangeStart = $today->subDays($range - 1)->utc();
        $rangeEnd = $today->endOfDay()->utc();
        $inRange = fn (): Builder => Booking::query()->whereBetween('created_at', [$rangeStart, $rangeEnd]);
        $monthlyTrend = collect(range(5, 0))->map(function (int $offset) use ($today): array {
            $month = $today->subMonths($offset)->startOfMonth();

            return [
                'label' => $month->format('M'),
                'count' => Booking::query()->whereBetween('created_at', [$month->utc(), $month->endOfMonth()->utc()])->count(),
            ];
        });
        $peak = max(1, (int) $monthlyTrend->max('count'));

        return view('admin.dashboard', [
            'range' => $range,
            'totalBookings' => $inRange()->count(),
            'newBookings' => $inRange()->whereIn('status', ['New', 'Pending'])->count(),
            'confirmedBookings' => $inRange()->whereIn('status', ['Confirmed', 'Scheduled'])->count(),
            'completedBookings' => $inRange()->where('status', 'Completed')->count(),
            'cancelledBookings' => $inRange()->where('status', 'Cancelled')->count(),
            'enquiries' => Enquiry::where('is_read', false)->count(),
            'monthlyTrend' => $monthlyTrend->map(fn (array $month): array => $month + ['height' => max(6, (int) round($month['count'] / $peak * 100))]),
            'serviceBreakdown' => $inRange()->selectRaw('service_id, count(*) as bookings_count')->with('service')->groupBy('service_id')->get(),
            'upcomingBookings' => Booking::with('service')->whereIn('status', ['Confirmed', 'Scheduled'])
                ->whereDate('preferred_date', '>=', $today->toDateString())->orderBy('preferred_date')->orderBy('preferred_time')->limit(5)->get(),
            'recentEnquiries' => Enquiry::query()->latest()->limit(5)->get(),
            'bookings' => $inRange()->with('service')->latest()->take(8)->get(),
        ]);
    }

    public function bookings(Request $request, BookingSchedule $schedule): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(BookingSchedule::STATUSES)],
            'service_id' => ['nullable', 'integer'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'period' => ['nullable', Rule::in(['day', 'week', 'month'])],
            'anchor' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $bookingsQuery = $this->filteredBookings($filters);
        $calendarStart = null;
        $calendarEnd = null;
        $calendarDays = collect();
        if (isset($filters['period'])) {
            $anchor = CarbonImmutable::parse($filters['anchor'] ?? now('Africa/Nairobi')->toDateString(), 'Africa/Nairobi');
            [$calendarStart, $calendarEnd] = match ($filters['period']) {
                'day' => [$anchor, $anchor],
                'week' => [$anchor->startOfWeek(CarbonImmutable::MONDAY), $anchor->endOfWeek(CarbonImmutable::SUNDAY)],
                default => [$anchor->startOfMonth(), $anchor->endOfMonth()],
            };
            $calendarDays = (clone $this->filteredBookings(collect($filters)->except('date')->all()))
                ->whereDate('preferred_date', '>=', $calendarStart->toDateString())
                ->whereDate('preferred_date', '<=', $calendarEnd->toDateString())
                ->orderBy('preferred_date')->orderBy('preferred_time')->get()->groupBy(fn (Booking $booking): string => $booking->preferred_date->toDateString());
            $bookingsQuery = (clone $this->filteredBookings(collect($filters)->except('date')->all()))
                ->whereDate('preferred_date', '>=', $calendarStart->toDateString())
                ->whereDate('preferred_date', '<=', $calendarEnd->toDateString());
        }
        $bookings = $bookingsQuery->latest()->paginate(20)->withQueryString();

        return view('admin.bookings', [
            'bookings' => $bookings,
            'services' => Service::query()->orderBy('sort_order')->get(),
            'statuses' => BookingSchedule::STATUSES,
            'schedule' => $schedule->settings(),
            'calendarDays' => $calendarDays,
            'calendarStart' => $calendarStart,
            'calendarEnd' => $calendarEnd,
        ]);
    }

    public function exportBookings(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(BookingSchedule::STATUSES)],
            'service_id' => ['nullable', 'integer'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $bookings = $this->filteredBookings($filters)->with('service')->orderBy('preferred_date')->orderBy('preferred_time')->cursor();

        return response()->streamDownload(function () use ($bookings): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Reference', 'Customer', 'Phone', 'Email', 'Service', 'Package', 'Price', 'Date', 'Time', 'Location', 'Pickup', 'Destination', 'Status', 'Instructions', 'Internal notes']);
            foreach ($bookings as $booking) {
                fputcsv($stream, array_map($this->safeCsvValue(...), [
                    $booking->reference, $booking->customer_name, $booking->phone, $booking->email, $booking->service?->name,
                    $booking->package_name, $booking->listed_price, $booking->preferred_date?->toDateString(), $booking->preferred_time,
                    $booking->location, $booking->pickup_location, $booking->destination, $booking->status, $booking->instructions, $booking->internal_notes,
                ]));
            }
            fclose($stream);
        }, 'bookings-'.now('Africa/Nairobi')->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function updateBooking(Request $request, Booking $booking, BookingSchedule $schedule): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(BookingSchedule::STATUSES)],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'preferred_date' => ['nullable', 'required_with:preferred_time', 'date_format:Y-m-d'],
            'preferred_time' => ['nullable', 'required_with:preferred_date', 'date_format:H:i'],
        ]);

        $changed = DB::transaction(function () use ($booking, $data, $schedule): bool {
            $schedule->lock();
            $booking->refresh();
            $rescheduled = isset($data['preferred_date'], $data['preferred_time'])
                && ($data['preferred_date'] !== $booking->preferred_date?->toDateString() || $data['preferred_time'] !== $booking->preferred_time);
            $activeStatus = ! in_array($data['status'], ['Cancelled', 'Rejected'], true);
            $reactivated = $activeStatus && in_array($booking->status, ['Cancelled', 'Rejected'], true);
            $dateValue = $data['preferred_date'] ?? $booking->preferred_date?->toDateString();
            $timeValue = $data['preferred_time'] ?? $booking->preferred_time;
            if ($activeStatus && ($rescheduled || $reactivated) && $dateValue && $timeValue) {
                $date = CarbonImmutable::parse($dateValue, 'Africa/Nairobi');
                $available = $schedule->availability($booking->service_id, $date, $date, $booking->id);
                if (! in_array($timeValue, $available[$dateValue], true)) {
                    throw ValidationException::withMessages(['preferred_time' => 'Choose an available date and time for this booking.']);
                }
            }

            $statusChanged = $booking->status !== $data['status'];
            $history = $booking->status_history ?? [];
            if ($statusChanged || $rescheduled) {
                $history[] = ['status' => $data['status'], 'at' => now()->toIso8601String(), 'date' => $dateValue, 'time' => $timeValue];
            }
            $booking->forceFill([
                'status' => $data['status'],
                'internal_notes' => $data['internal_notes'] ?? null,
                'preferred_date' => $dateValue,
                'preferred_time' => $timeValue,
                'scheduled_duration' => ($rescheduled || $reactivated) ? $schedule->duration($booking->service_id) : $booking->scheduled_duration,
                'status_history' => $history,
            ])->save();

            return $statusChanged || $rescheduled;
        }, attempts: 5);

        if ($changed && $booking->email) {
            try {
                $url = URL::temporarySignedRoute('booking.confirmation', now()->addDays(30), ['reference' => $booking->reference]);
                Notification::route('mail', $booking->email)->notify(new BookingUpdate($booking, false, $url));
            } catch (Throwable $exception) {
                report($exception);

                return back()->with('success', 'Booking updated. The customer email could not be sent.');
            }
        }

        return back()->with('success', 'Booking updated.');
    }

    public function updateSchedule(Request $request, BookingSchedule $schedule): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*' => ['integer', 'between:1,7', 'distinct'],
            'hours' => ['required', 'array', 'min:1'],
            'hours.*' => ['required', 'string', 'distinct', Rule::in(array_keys(BookingSchedule::HOURS))],
            'interval' => ['required', 'integer', 'between:15,240'],
            'capacity' => ['required', 'integer', 'between:1,20'],
            'notice' => ['required', 'integer', 'between:0,336'],
            'blocked_dates' => ['nullable', 'string', 'max:5000'],
            'service_rules' => ['nullable', 'array'],
            'service_rules.*.duration' => ['nullable', 'integer', 'between:15,480'],
            'booking_recipient' => ['nullable', 'email', 'max:255'],
            'enquiry_recipient' => ['nullable', 'email', 'max:255'],
        ]);
        $blocked = preg_split('/\s+/', trim($data['blocked_dates'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        validator(['dates' => $blocked], ['dates.*' => ['date_format:Y-m-d']])->validate();
        $rules = [];
        $hours = array_values(array_intersect(array_keys(BookingSchedule::HOURS), $data['hours']));
        foreach (Service::query()->pluck('id') as $id) {
            if ($duration = $data['service_rules'][$id]['duration'] ?? null) {
                $rules[$id] = ['duration' => (int) $duration];
            }
        }

        DB::transaction(function () use ($data, $blocked, $rules, $schedule): void {
            $schedule->lock();
            SiteSetting::updateOrCreate(['key' => 'booking_schedule'], ['value' => json_encode([
                'enabled' => (bool) $data['enabled'],
                'weekdays' => array_map('intval', $data['weekdays']),
                'hours' => $hours,
                'interval' => (int) $data['interval'], 'capacity' => (int) $data['capacity'], 'notice' => (int) $data['notice'],
                'blocked_dates' => array_values(array_unique($blocked)), 'service_rules' => $rules,
            ], JSON_THROW_ON_ERROR)]);
            foreach (['booking_recipient', 'enquiry_recipient'] as $key) {
                SiteSetting::updateOrCreate(['key' => $key], ['value' => $data[$key] ?? null]);
            }
        }, attempts: 5);

        return back()->with('success', 'Booking schedule saved.');
    }

    public function pricing(): View
    {
        return view('admin.pricing', ['services' => Service::with('packages')->orderBy('sort_order')->get()]);
    }

    public function services(): View
    {
        return view('admin.services', ['services' => Service::with('packages')->orderBy('sort_order')->get()]);
    }

    public function updateService(Request $request, Service $service, ImageOptimizer $optimizer): RedirectResponse
    {
        return $this->saveService($request, $service, $optimizer);
    }

    public function createService(Request $request, ImageOptimizer $optimizer): RedirectResponse
    {
        return $this->saveService($request, new Service, $optimizer);
    }

    private function saveService(Request $request, Service $service, ImageOptimizer $optimizer): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'summary' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'], 'is_active' => ['sometimes', 'boolean'],
            'slug' => [$service->exists ? 'sometimes' : 'required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('services')->ignore($service->id)],
            'sort_order' => ['nullable', 'integer', 'between:0,9999'],
            'content' => ['nullable', 'string', 'max:20000'],
            'benefits_text' => ['nullable', 'string', 'max:4000'],
            'faqs_text' => ['nullable', 'string', 'max:10000'],
            'seo_title' => ['nullable', 'string', 'max:150'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'seo_robots' => ['nullable', Rule::in(['index, follow', 'noindex, follow', 'noindex, nofollow'])],
            'og_title' => ['nullable', 'string', 'max:150'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'og_image' => ['nullable', 'url', 'max:2048'],
            'service_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);
        if ($service->exists && isset($data['slug']) && $data['slug'] !== $service->slug) {
            return back()->withErrors(['slug' => 'The URL of an existing service cannot be changed here.']);
        }
        $lines = fn (string $value): array => array_values(array_filter(array_map('trim', preg_split('/\R/', $value))));
        if (array_key_exists('benefits_text', $data)) {
            $data['benefits'] = $lines($data['benefits_text'] ?? '');
        }
        if (array_key_exists('faqs_text', $data)) {
            $data['faqs'] = [];
            foreach ($lines($data['faqs_text'] ?? '') as $line) {
                [$question, $answer] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
                if ($question === '' || $answer === '') {
                    return back()->withErrors(['faqs_text' => 'Enter each FAQ as a question, a vertical bar, then its answer.'])->withInput();
                }
                $data['faqs'][] = compact('question', 'answer');
            }
        }
        if ($request->hasFile('service_photo')) {
            $data['image'] = 'storage/'.$optimizer->store($request->file('service_photo'), 'site');
        }
        $data['sort_order'] = $data['sort_order'] ?? $service->sort_order ?? 0;
        $service->fill($data)->fill(['is_active' => $request->boolean('is_active')])->save();

        return back()->with('success', 'Service details updated.');
    }

    public function updatePrice(Request $request, PricePackage $package): RedirectResponse
    {
        return $this->savePrice($request, $package);
    }

    public function createPrice(Request $request, Service $service): RedirectResponse
    {
        return $this->savePrice($request, new PricePackage(['service_id' => $service->id]));
    }

    private function savePrice(Request $request, PricePackage $package): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'required_unless:pricing_type,quote', 'integer', 'min:0', 'max:10000000'],
            'pricing_type' => ['required', Rule::in(['starting', 'fixed', 'quote'])],
            'sort_order' => ['required', 'integer', 'between:0,9999'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['amount'] = $data['pricing_type'] === 'quote' ? 0 : $data['amount'];
        $package->fill($data)->fill(['is_active' => $request->boolean('is_active'), 'is_featured' => $request->boolean('is_featured')])->save();

        return back()->with('success', 'Price updated.');
    }

    public function settings(): View
    {
        return view('admin.settings', ['settings' => SiteSetting::pluck('value', 'key')]);
    }

    public function staff(): View
    {
        return view('admin.staff', ['staffMembers' => User::query()->orderBy('name')->paginate(30)]);
    }

    public function testimonials(): View
    {
        return view('admin.testimonials', ['testimonials' => Testimonial::query()->orderBy('sort_order')->latest()->paginate(20)]);
    }

    public function createTestimonial(Request $request): RedirectResponse
    {
        $data = $this->validatedTestimonial($request);
        Testimonial::create($data + ['is_published' => $request->boolean('is_published')]);

        return back()->with('success', 'Customer feedback saved.');
    }

    public function updateTestimonial(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($this->validatedTestimonial($request) + ['is_published' => $request->boolean('is_published')]);

        return back()->with('success', 'Customer feedback updated.');
    }

    public function deleteTestimonial(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return back()->with('success', 'Customer feedback removed.');
    }

    /** @return array{customer_name: string, service: ?string, quote: string, sort_order: int} */
    private function validatedTestimonial(Request $request): array
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'service' => ['nullable', 'string', 'max:120'],
            'quote' => ['required', 'string', 'max:1200'],
            'sort_order' => ['required', 'integer', 'between:0,9999'],
            'consent_confirmed' => ['required', 'accepted'],
        ]);
        unset($data['consent_confirmed']);

        return $data + ['consent_confirmed_at' => now()];
    }

    public function updateStaffRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['role' => ['nullable', Rule::in(['administrator', 'booking_manager', 'content_editor'])]]);
        if ($user->is_admin) {
            return back()->withErrors(['role' => 'Primary administrators keep their existing full access.']);
        }
        $user->role = $data['role'] ?? null;
        $user->save();

        return back()->with('success', 'Staff access updated.');
    }

    public function homepageSettings(): View
    {
        $defaults = config('site_homepage');
        $content = json_decode(SiteSetting::query()->where('key', 'homepage_content')->value('value') ?? '', true) ?: [];

        return view('admin.homepage', compact('defaults', 'content'));
    }

    public function updateHomepage(Request $request, ImageOptimizer $optimizer): RedirectResponse
    {
        $defaults = config('site_homepage');
        $rules = [
            'images.hero' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'images.about' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'images.steps_team' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'images.steps_result' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
        foreach ($defaults as $section => $fields) {
            $rules['position.'.$section] = ['required', 'integer', 'between:1,99'];
            $rules['visible.'.$section] = ['sometimes', 'boolean'];
            foreach ($fields as $key => $value) {
                $rules['copy.'.$section.'.'.$key] = ['required', 'string', 'max:500'];
            }
        }
        $data = $request->validate($rules);
        $settings = SiteSetting::query()->firstOrNew(['key' => 'homepage_content']);
        $previous = json_decode($settings->value ?? '', true) ?: [];
        $images = $previous['images'] ?? [];
        foreach (array_keys($data['images'] ?? []) as $image) {
            if ($request->hasFile('images.'.$image)) {
                $images[$image] = 'storage/'.$optimizer->store($request->file('images.'.$image), 'site');
            }
        }
        $order = array_filter($data['position'], fn (string $section): bool => $section === 'hero' || $request->boolean('visible.'.$section), ARRAY_FILTER_USE_KEY);
        asort($order, SORT_NUMERIC);
        $copy = [];
        foreach ($data['copy'] as $section => $fields) {
            foreach ($fields as $key => $value) {
                $copy[$section.'.'.$key] = $value;
            }
        }
        $settings->value = json_encode(['copy' => $copy, 'order' => array_keys($order), 'images' => $images], JSON_THROW_ON_ERROR);
        $settings->save();

        return back()->with('success', 'Homepage content saved.');
    }

    public function updateSettings(Request $request, ImageOptimizer $optimizer): RedirectResponse
    {
        $navigationRoutes = ['home', 'about', 'services', 'pricing', 'booking', 'contact'];
        $navigationRules = [];
        foreach (array_keys($navigationRoutes) as $index) {
            $number = $index + 1;
            $navigationRules['footer_link_'.$number.'_label'] = ['nullable', 'string', 'max:80'];
            $navigationRules['footer_link_'.$number.'_route'] = ['required', 'in:'.implode(',', $navigationRoutes)];
        }
        $pageSeoRules = [];
        foreach (['home', 'services', 'pricing', 'gallery', 'booking', 'contact', 'blog'] as $seoRoute) {
            if ($seoRoute !== 'home') {
                $pageSeoRules['seo_'.$seoRoute.'_title'] = ['nullable', 'string', 'max:150'];
                $pageSeoRules['seo_'.$seoRoute.'_description'] = ['nullable', 'string', 'max:320'];
            }
            $pageSeoRules['seo_'.$seoRoute.'_canonical_url'] = ['nullable', 'url', 'max:2048'];
            $pageSeoRules['seo_'.$seoRoute.'_robots'] = ['nullable', Rule::in(['index, follow', 'noindex, follow', 'noindex, nofollow'])];
            $pageSeoRules['seo_'.$seoRoute.'_og_title'] = ['nullable', 'string', 'max:150'];
            $pageSeoRules['seo_'.$seoRoute.'_og_description'] = ['nullable', 'string', 'max:320'];
            $pageSeoRules['seo_'.$seoRoute.'_og_image'] = ['nullable', 'url', 'max:2048'];
        }

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:150'], 'tagline' => ['nullable', 'string', 'max:200'],
            'hero_heading' => ['required', 'string', 'max:200'], 'hero_description' => ['required', 'string', 'max:500'],
            'about_preview' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:40'], 'whatsapp' => ['required', 'string', 'max:40'],
            'facebook' => ['nullable', 'url', 'max:255'], 'instagram' => ['nullable', 'url', 'max:255'], 'tiktok' => ['nullable', 'url', 'max:255'], 'youtube' => ['nullable', 'url', 'max:255'],
            'business_email' => ['nullable', 'email', 'max:255'], 'business_location' => ['nullable', 'string', 'max:160'],
            'footer_description' => ['nullable', 'string', 'max:500'], 'footer_house_watermark' => ['sometimes', 'boolean'],
            'footer_logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'cta_eyebrow' => ['nullable', 'string', 'max:100'], 'cta_heading' => ['nullable', 'string', 'max:200'],
            'cta_description' => ['nullable', 'string', 'max:500'], 'cta_booking_label' => ['nullable', 'string', 'max:80'],
            'cta_booking_destination' => ['required', 'in:booking,services,contact'], 'cta_whatsapp_label' => ['nullable', 'string', 'max:80'],
            'cta_whatsapp_destination' => ['required', 'in:whatsapp,contact'], 'cta_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'seo_title' => ['nullable', 'string', 'max:150'], 'seo_description' => ['nullable', 'string', 'max:320'],
            'seo_robots' => ['nullable', Rule::in(['index, follow', 'noindex, follow', 'noindex, nofollow'])],
            'seo_title_suffix' => ['nullable', 'string', 'max:100'],
            'google_site_verification' => ['nullable', 'regex:/^[A-Za-z0-9_-]{10,200}$/'],
            'ga4_measurement_id' => ['nullable', 'regex:/^G-[A-Z0-9]+$/'],
            'gtm_container_id' => ['nullable', 'regex:/^GTM-[A-Z0-9]+$/'],
            'operating_hours' => ['nullable', 'string', 'max:250'],
            'social_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'primary_logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'favicon_file' => ['nullable', 'image', 'mimes:png', 'max:1024'],
        ] + $navigationRules + $pageSeoRules);

        $data['footer_house_watermark'] = $request->boolean('footer_house_watermark') ? '1' : '0';
        foreach (['footer_logo_file' => 'footer_logo', 'cta_image_file' => 'cta_image', 'social_image_file' => 'social_image', 'primary_logo_file' => 'primary_logo', 'favicon_file' => 'favicon'] as $fileField => $settingKey) {
            if ($request->hasFile($fileField)) {
                $data[$settingKey] = 'storage/'.$optimizer->store($request->file($fileField), 'site');
            }
            unset($data[$fileField]);
        }

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Website settings saved.');
    }

    public function enquiries(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['new', 'in_progress', 'responded', 'archived'])],
        ]);
        $enquiries = Enquiry::query()->with('service')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->whereLike('name', '%'.$search.'%')->orWhereLike('phone', '%'.$search.'%')
                        ->orWhereLike('email', '%'.$search.'%')->orWhereLike('subject', '%'.$search.'%');
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.enquiries', compact('enquiries'));
    }

    public function updateEnquiry(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'in_progress', 'responded', 'archived'])],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'is_read' => ['required', 'boolean'],
        ]);
        $enquiry->update($data);

        return back()->with('success', 'Enquiry updated.');
    }

    public function exportEnquiries(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['new', 'in_progress', 'responded', 'archived'])],
        ]);
        $enquiries = Enquiry::query()->with('service')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->whereLike('name', '%'.$search.'%')->orWhereLike('phone', '%'.$search.'%')
                        ->orWhereLike('email', '%'.$search.'%')->orWhereLike('subject', '%'.$search.'%');
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()->cursor();

        return response()->streamDownload(function () use ($enquiries): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Name', 'Phone', 'Email', 'Subject', 'Service', 'Message', 'Status', 'Internal notes', 'Received']);
            foreach ($enquiries as $enquiry) {
                fputcsv($stream, array_map($this->safeCsvValue(...), [
                    $enquiry->name, $enquiry->phone, $enquiry->email, $enquiry->subject, $enquiry->service?->name,
                    $enquiry->message, $enquiry->status, $enquiry->internal_notes, $enquiry->created_at?->toIso8601String(),
                ]));
            }
            fclose($stream);
        }, 'enquiries-'.now('Africa/Nairobi')->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filteredBookings(array $filters): Builder
    {
        return Booking::query()->with('service')
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereLike('reference', '%'.$search.'%')->orWhereLike('customer_name', '%'.$search.'%')->orWhereLike('phone', '%'.$search.'%');
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['service_id'] ?? null, fn (Builder $query, int $service) => $query->where('service_id', $service))
            ->when($filters['date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('preferred_date', $date));
    }

    private function safeCsvValue(mixed $value): string|int|float|null
    {
        if (is_string($value) && preg_match('/^[\s]*[=+@\-]/', $value)) {
            return "'".$value;
        }

        return $value;
    }

    public function gallery(): View
    {
        return view('admin.gallery', ['items' => GalleryItem::latest()->paginate(24)]);
    }

    public function media(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', Rule::in(['moving', 'fumigation', 'upholstery', 'home', 'brand', 'other'])],
        ]);
        $assets = MediaAsset::query()->with('uploadedBy')
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereLike('original_name', '%'.$search.'%')->orWhereLike('title', '%'.$search.'%')->orWhereLike('alt_text', '%'.$search.'%');
                });
            })
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category))
            ->latest()->paginate(20)->withQueryString();
        $assets->getCollection()->each(function (MediaAsset $asset): void {
            $asset->setAttribute('usage_count', $this->mediaUsageCount($asset));
        });

        return view('admin.media', compact('assets'));
    }

    public function uploadMedia(Request $request, ImageOptimizer $optimizer): RedirectResponse
    {
        $data = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'category' => ['required', Rule::in(['moving', 'fumigation', 'upholstery', 'home', 'brand', 'other'])],
        ]);
        foreach ($data['files'] as $upload) {
            $path = $optimizer->store($upload, 'media');
            MediaAsset::create([
                'path' => $path,
                'original_name' => mb_substr($upload->getClientOriginalName(), 0, 255),
                'title' => pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME),
                'alt_text' => pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME),
                'category' => $data['category'],
                'mime_type' => Storage::disk('public')->mimeType($path) ?? 'image/webp',
                'size' => Storage::disk('public')->size($path),
                'uploaded_by' => $request->user()->id,
            ]);
        }

        return back()->with('success', 'Images added to the media library. Review the titles and alt text before using them.');
    }

    public function updateMedia(Request $request, MediaAsset $asset, ImageOptimizer $optimizer): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'alt_text' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['moving', 'fumigation', 'upholstery', 'home', 'brand', 'other'])],
            'replacement' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);
        $previousPath = $asset->path;
        if ($request->hasFile('replacement')) {
            $asset->path = $optimizer->store($request->file('replacement'), 'media');
            $data['original_name'] = mb_substr($request->file('replacement')->getClientOriginalName(), 0, 255);
            $this->replaceMediaReferences($previousPath, $asset->path);
            $asset->mime_type = Storage::disk('public')->mimeType($asset->path) ?? 'image/webp';
            $asset->size = Storage::disk('public')->size($asset->path);
        }
        unset($data['replacement']);
        $asset->fill($data)->save();
        if ($previousPath !== $asset->path && ! MediaAsset::query()->where('path', $previousPath)->exists()) {
            Storage::disk('public')->delete($previousPath);
        }

        return back()->with('success', 'Media details saved.');
    }

    public function deleteMedia(MediaAsset $asset): RedirectResponse
    {
        if ($this->mediaUsageCount($asset) > 0) {
            return back()->withErrors(['media' => 'This image is currently used by website content. Replace or remove those references before deleting it.']);
        }

        $path = $asset->path;
        $asset->delete();
        Storage::disk('public')->delete($path);

        return back()->with('success', 'Image removed from the media library.');
    }

    private function mediaUsageCount(MediaAsset $asset): int
    {
        $publicPath = 'storage/'.$asset->path;
        $settingReference = SiteSetting::query()->where('value', $publicPath)->exists();
        $homePageReference = SiteSetting::query()->where('key', 'homepage_content')->whereLike('value', '%'.$asset->path.'%')->exists();

        return Service::query()->where('image', $publicPath)->count()
            + GalleryItem::query()->where('path', $asset->path)->count()
            + ContentPage::query()->whereIn('image', [$asset->path, $publicPath])->count()
            + (int) $settingReference + (int) $homePageReference;
    }

    private function replaceMediaReferences(string $previousPath, string $replacementPath): void
    {
        $previousPublicPath = 'storage/'.$previousPath;
        $replacementPublicPath = 'storage/'.$replacementPath;
        Service::query()->where('image', $previousPublicPath)->update(['image' => $replacementPublicPath]);
        GalleryItem::query()->where('path', $previousPath)->update(['path' => $replacementPath]);
        ContentPage::query()->where('image', $previousPath)->update(['image' => $replacementPath]);
        ContentPage::query()->where('image', $previousPublicPath)->update(['image' => $replacementPublicPath]);
        SiteSetting::query()->where('value', $previousPublicPath)->update(['value' => $replacementPublicPath]);

        $homepage = SiteSetting::query()->where('key', 'homepage_content')->first();
        if ($homepage && str_contains($homepage->value ?? '', $previousPublicPath)) {
            $homepage->value = str_replace($previousPublicPath, $replacementPublicPath, $homepage->value);
            $homepage->save();
        }
    }

    public function uploadGalleryItem(Request $request, ImageOptimizer $optimizer): RedirectResponse
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'caption' => ['nullable', 'string', 'max:160'], 'alt_text' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(GalleryItem::CATEGORIES)],
        ]);
        $path = $optimizer->store($request->file('image'), 'gallery');
        GalleryItem::create($data + ['path' => $path, 'is_published' => true]);

        return back()->with('success', 'Gallery image uploaded.');
    }

    public function updateGalleryItem(Request $request, GalleryItem $item, ImageOptimizer $optimizer): RedirectResponse
    {
        $data = $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'caption' => ['nullable', 'string', 'max:160'],
            'alt_text' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(GalleryItem::CATEGORIES)],
            'sort_order' => ['required', 'integer', 'between:0,9999'],
            'is_published' => ['sometimes', 'boolean'],
        ]);
        $previous = $item->path;
        if ($request->hasFile('image')) {
            $data['path'] = $optimizer->store($request->file('image'), 'gallery');
        }
        unset($data['image']);
        $item->update($data + ['is_published' => $request->boolean('is_published')]);

        if ($item->path !== $previous && ! GalleryItem::query()->where('path', $previous)->exists()) {
            Storage::disk('public')->delete($previous);
        }

        return back()->with('success', 'Gallery image updated.');
    }

    public function deleteGalleryItem(GalleryItem $item): RedirectResponse
    {
        $path = $item->path;
        $item->delete();
        if (! GalleryItem::query()->where('path', $path)->exists()) {
            Storage::disk('public')->delete($path);
        }

        return back()->with('success', 'Gallery image removed.');
    }
}
