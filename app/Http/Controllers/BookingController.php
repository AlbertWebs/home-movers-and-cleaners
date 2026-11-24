<?php

namespace App\Http\Controllers;

use App\BookingSchedule;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\PricePackage;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Notifications\BookingUpdate;
use App\Notifications\EnquiryReceived;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BookingController extends Controller
{
    public function availability(Request $request, BookingSchedule $schedule): JsonResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'month' => ['required', 'date_format:Y-m'],
        ]);
        $month = CarbonImmutable::createFromFormat('!Y-m', $data['month'], 'Africa/Nairobi');
        $current = $schedule->now()->startOfMonth();
        abort_if($month->lt($current) || $month->gt($current->addMonths(5)), 404);

        return response()->json(['dates' => $schedule->availability((int) $data['service_id'], $month, $month->endOfMonth()->startOfDay())])
            ->header('Cache-Control', 'no-store');
    }

    public function store(Request $request, BookingSchedule $schedule): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'request_token' => ['required', 'uuid'],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'price_package_id' => ['nullable', 'integer'],
            'customer_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[+0-9 ()-]{7,30}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'preferred_date' => ['required', 'date_format:Y-m-d'],
            'preferred_time' => ['required', 'date_format:H:i'],
            'instructions' => ['nullable', 'string', 'max:3000'],
            'quote_photos' => ['nullable', 'array', 'max:5'],
            'quote_photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
        ]);

        [$booking, $created] = DB::transaction(function () use ($data, $schedule, $request): array {
            $schedule->lock();

            $existing = Booking::query()->where('request_token', $data['request_token'])->first();
            if ($existing && $existing->phone === $data['phone'] && $existing->customer_name === $data['customer_name']) {
                return [$existing, false];
            }
            abort_if($existing, 409, 'This request has already been used.');

            $service = Service::query()->where('is_active', true)->findOrFail($data['service_id']);
            $moving = $service->slug === 'moving-services';
            $required = $moving ? ['pickup_location', 'destination'] : ['location'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw ValidationException::withMessages([$field => 'Enter the '.str_replace('_', ' ', $field).'.']);
                }
            }

            $package = isset($data['price_package_id'])
                ? PricePackage::query()->whereBelongsTo($service)->where('is_active', true)->find($data['price_package_id'])
                : null;
            if (isset($data['price_package_id']) && ! $package) {
                throw ValidationException::withMessages(['price_package_id' => 'Choose an available package for this service.']);
            }
            if ($request->hasFile('quote_photos') && $package && $package->pricing_type !== 'quote') {
                throw ValidationException::withMessages(['quote_photos' => 'Photos can be attached to quote requests. Choose Request a Quote to add photos.']);
            }

            $date = CarbonImmutable::parse($data['preferred_date'], 'Africa/Nairobi');
            $available = $schedule->availability($service->id, $date, $date);
            if (! in_array($data['preferred_time'], $available[$data['preferred_date']], true)) {
                throw ValidationException::withMessages(['preferred_time' => 'This time is no longer available. Choose another date or time.']);
            }

            $booking = new Booking(array_diff_key($data, ['request_token' => true, 'quote_photos' => true]));
            $booking->forceFill([
                'request_token' => $data['request_token'],
                'reference' => 'HMC-'.$schedule->now()->year.'-'.Str::upper(Str::random(10)),
                'location' => $moving ? $data['pickup_location'] : $data['location'],
                'package_name' => $package?->name ?? 'Request a Quote',
                'listed_price' => $package && $package->pricing_type !== 'quote' ? $package->amount : 0,
                'scheduled_duration' => $schedule->duration($service->id),
                'status' => 'Pending',
                'status_history' => [['status' => 'Pending', 'at' => now()->toIso8601String()]],
            ])->save();

            return [$booking, true];
        }, attempts: 5);

        $photosAdded = false;
        if ($request->hasFile('quote_photos')) {
            $isQuoteRequest = ! $booking->price_package_id || $booking->pricePackage?->pricing_type === 'quote';
            if (! $isQuoteRequest) {
                throw ValidationException::withMessages(['quote_photos' => 'Photos can be attached to quote requests. Choose Request a Quote to add photos.']);
            }

            if (empty($booking->quote_photos)) {
                $storedPhotos = [];
                try {
                    $booking = DB::transaction(function () use ($booking, $request, &$storedPhotos): Booking {
                        $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->getKey());
                        if (empty($lockedBooking->quote_photos)) {
                            foreach ($request->file('quote_photos') as $photo) {
                                $path = $photo->store('booking-quotes/'.$lockedBooking->getKey(), 'local');
                                if (! $path) {
                                    throw new \RuntimeException('A quote photo could not be stored.');
                                }
                                $storedPhotos[] = $path;
                            }
                            $lockedBooking->forceFill(['quote_photos' => $storedPhotos])->save();
                        }

                        return $lockedBooking;
                    }, attempts: 1);
                    $photosAdded = count($storedPhotos) > 0;
                } catch (Throwable $exception) {
                    Storage::disk('local')->delete($storedPhotos);
                    report($exception);
                    throw ValidationException::withMessages(['quote_photos' => 'We could not save the selected photos. Please try again.']);
                }
            }
        }

        $url = URL::temporarySignedRoute('booking.confirmation', now()->addDays(30), ['reference' => $booking->reference]);
        if ($created || $photosAdded) {
            $this->notifyBooking($booking, $url);
        }

        return $request->expectsJson()
            ? response()->json(['reference' => $booking->reference, 'redirect' => $url], $created ? 201 : 200)
            : redirect()->to($url);
    }

    public function confirmation(string $reference): Response
    {
        $booking = Booking::with('service')->where('reference', $reference)->firstOrFail();

        return response()->view('pages.booking-confirmation', compact('booking'))
            ->header('Cache-Control', 'private, no-store')
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function quotePhoto(Booking $booking, int $photo): StreamedResponse
    {
        $path = $booking->quote_photos[$photo] ?? null;
        abort_unless(is_string($path) && Str::startsWith($path, 'booking-quotes/'.$booking->getKey().'/'), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, basename($path), ['X-Content-Type-Options' => 'nosniff']);
    }

    public function enquiry(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:4000'],
            'website' => ['nullable', 'size:0'],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
        ]);
        unset($data['website']);
        $enquiry = Enquiry::create($data);
        $recipient = SiteSetting::query()->where('key', 'enquiry_recipient')->value('value');
        if ($recipient) {
            try {
                Notification::route('mail', $recipient)->notify(new EnquiryReceived($enquiry));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $message = 'Thanks for reaching out. Your message has been received.';

        return $request->expectsJson() ? response()->json(['message' => $message], 201) : back()->with('success', $message);
    }

    private function notifyBooking(Booking $booking, string $url): void
    {
        $admin = SiteSetting::query()->where('key', 'booking_recipient')->value('value');
        foreach ([[$booking->email, false], [$admin, true]] as [$recipient, $isAdmin]) {
            if (! $recipient) {
                continue;
            }

            try {
                Notification::route('mail', $recipient)->notify(new BookingUpdate($booking, $isAdmin, $isAdmin ? route('admin.bookings') : $url));
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}
