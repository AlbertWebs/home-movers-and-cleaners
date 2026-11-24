<?php

namespace Tests\Feature;

use App\BookingSchedule;
use App\Models\Booking;
use App\Models\PricePackage;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\BookingUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_is_pending_idempotent_and_requires_a_signed_status_link(): void
    {
        Notification::fake();
        $this->travelTo(now('Africa/Nairobi')->setDate(2026, 11, 16)->setTime(10, 0));
        $payload = $this->payload($this->service());

        $response = $this->postJson(route('booking.store'), $payload)->assertCreated();
        $reference = $response->json('reference');

        $this->assertDatabaseHas('bookings', ['reference' => $reference, 'status' => 'Pending']);
        $this->get(route('booking.confirmation', $reference))->assertForbidden();
        $this->get($response->json('redirect'))->assertOk()->assertSee('Pending')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->postJson(route('booking.store'), $payload)->assertOk()->assertJsonPath('reference', $reference);
        $this->assertDatabaseCount('bookings', 1);
        Notification::assertSentOnDemandTimes(BookingUpdate::class, 1);
    }

    public function test_booking_email_uses_branded_html_and_escaped_customer_content(): void
    {
        $service = $this->service();
        SiteSetting::create(['key' => 'notification_cc', 'value' => 'albertmuhatia@gmail.com, homemoversandcleaners@gmail.com']);
        $booking = Booking::create([
            'reference' => 'HMC-2026-EMAIL01', 'service_id' => $service->id, 'package_name' => 'Request a Quote',
            'listed_price' => 0, 'customer_name' => '<script>alert(1)</script>', 'phone' => '0799000000',
            'location' => 'Nairobi', 'preferred_date' => '2026-11-20', 'preferred_time' => '09:00', 'status' => 'Quoted', 'quote_issued_at' => now(),
        ]);
        $mail = (new BookingUpdate($booking, false, 'https://example.test/booking/status'))->toMail(new AnonymousNotifiable);
        $html = view($mail->view['html'], $mail->data())->render();

        $this->assertStringContainsString('Home Movers and Cleaners', $html);
        $this->assertStringContainsString('HMC-2026-EMAIL01', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('Quote valid until', $html);
        $this->assertSame([], $mail->cc);

        $adminMail = (new BookingUpdate($booking, true, 'https://example.test/admin/bookings'))->toMail(new AnonymousNotifiable);
        $this->assertSame([
            ['albertmuhatia@gmail.com', null],
            ['homemoversandcleaners@gmail.com', null],
        ], $adminMail->cc);
    }

    public function test_quote_photos_are_saved_privately_and_only_booking_managers_can_download_them(): void
    {
        Notification::fake();
        Storage::fake('local');
        $this->travelTo(now('Africa/Nairobi')->setDate(2026, 11, 16)->setTime(10, 0));
        $payload = $this->payload($this->service());
        $payload['quote_photos'] = [UploadedFile::fake()->image('living-room.jpg')->size(250)];

        $response = $this->postJson(route('booking.store'), $payload)->assertCreated();
        $booking = Booking::query()->where('reference', $response->json('reference'))->firstOrFail();
        $photoPath = $booking->quote_photos[0];

        Storage::disk('local')->assertExists($photoPath);
        $this->assertStringStartsWith('booking-quotes/'.$booking->id.'/', $photoPath);
        $this->actingAs(User::factory()->create())->get(route('admin.bookings.photos.show', [$booking, 0]))->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.bookings.photos.show', [$booking, 0]))
            ->assertOk();
        Notification::assertSentOnDemandTimes(BookingUpdate::class, 1);
    }

    public function test_quote_photos_are_rejected_for_a_listed_price_without_creating_a_booking(): void
    {
        Notification::fake();
        Storage::fake('local');
        $this->travelTo(now('Africa/Nairobi')->setDate(2026, 11, 16)->setTime(10, 0));
        $service = $this->service();
        $package = PricePackage::create([
            'service_id' => $service->id, 'name' => 'Standard clean', 'amount' => 5000,
            'pricing_type' => 'starting', 'is_active' => true, 'sort_order' => 0,
        ]);
        $payload = $this->payload($service);
        $payload['price_package_id'] = $package->id;
        $payload['quote_photos'] = [UploadedFile::fake()->image('living-room.jpg')];

        $this->postJson(route('booking.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quote_photos');

        $this->assertDatabaseCount('bookings', 0);
        Storage::disk('local')->assertEmpty();
        Notification::assertSentOnDemandTimes(BookingUpdate::class, 0);
    }

    public function test_availability_rejects_past_blocked_and_occupied_times_and_reopens_after_cancellation(): void
    {
        Notification::fake();
        $this->travelTo(now('Africa/Nairobi')->setDate(2026, 11, 16)->setTime(10, 0));
        $service = $this->service();
        SiteSetting::create(['key' => 'booking_schedule', 'value' => json_encode(['blocked_dates' => ['2026-11-18']])]);
        $payload = $this->payload($service);

        $this->postJson(route('booking.store'), array_replace($payload, ['preferred_date' => '2026-11-15']))
            ->assertUnprocessable()->assertJsonValidationErrors('preferred_time');
        $this->postJson(route('booking.store'), array_replace($payload, ['preferred_date' => '2026-11-18']))
            ->assertUnprocessable()->assertJsonValidationErrors('preferred_time');
        $this->postJson(route('booking.store'), $payload)->assertCreated();
        $this->postJson(route('booking.store'), array_replace($payload, ['request_token' => (string) Str::uuid()]))
            ->assertUnprocessable()->assertJsonValidationErrors('preferred_time');
        $this->getJson(route('booking.availability', ['service_id' => $service->id, 'month' => '2026-11']))
            ->assertOk()->assertJsonPath('dates.2026-11-19', ['06:00', '07:00', '12:00', '13:00', '14:00', '15:00']);

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->patch(route('admin.bookings.update', 1), ['status' => 'Cancelled'])->assertSessionHasNoErrors();
        $this->getJson(route('booking.availability', ['service_id' => $service->id, 'month' => '2026-11']))
            ->assertOk()->assertJsonPath('dates.2026-11-19', ['06:00', '07:00', '11:00', '12:00', '13:00', '14:00', '15:00']);
    }

    public function test_contact_request_saves_selected_service_and_returns_json(): void
    {
        Notification::fake();
        $service = $this->service();

        $this->postJson(route('contact.store'), [
            'name' => 'Example Customer', 'phone' => '0799000000', 'subject' => 'Cleaning enquiry',
            'service_id' => $service->id, 'message' => 'Please tell me what is included.',
        ])->assertCreated();

        $this->assertDatabaseHas('enquiries', ['service_id' => $service->id, 'subject' => 'Cleaning enquiry']);
    }

    public function test_booking_schedule_is_restricted_to_administrators_and_updates_availability(): void
    {
        $service = $this->service();
        $this->travelTo(now('Africa/Nairobi')->setDate(2026, 11, 16)->setTime(10, 0));
        $settings = BookingSchedule::DEFAULTS;
        $settings['enabled'] = false;

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.bookings.schedule'), $settings)->assertForbidden();
        $payload = array_replace($settings, [
            'blocked_dates' => '',
            'booking_recipient' => 'bookings@homemoversandcleaners.co.ke',
            'enquiry_recipient' => 'bookings@homemoversandcleaners.co.ke',
            'notification_cc' => 'albertmuhatia@gmail.com; homemoversandcleaners@gmail.com',
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->patch(route('admin.bookings.schedule'), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('site_settings', ['key' => 'booking_recipient', 'value' => 'bookings@homemoversandcleaners.co.ke']);
        $this->assertDatabaseHas('site_settings', ['key' => 'enquiry_recipient', 'value' => 'bookings@homemoversandcleaners.co.ke']);
        $this->assertDatabaseHas('site_settings', ['key' => 'notification_cc', 'value' => 'albertmuhatia@gmail.com, homemoversandcleaners@gmail.com']);
        $this->getJson(route('booking.availability', ['service_id' => $service->id, 'month' => '2026-11']))
            ->assertOk()->assertJsonPath('dates.2026-11-19', []);
    }

    public function test_quoted_bookings_expire_after_thirty_days_and_notify_the_customer(): void
    {
        $this->travelTo(now('UTC')->setDate(2026, 11, 24)->setTime(10, 0));
        $service = $this->service();
        $expiredQuote = Booking::create([
            'reference' => 'HMC-2026-EXPIRED1', 'service_id' => $service->id, 'package_name' => 'Request a Quote',
            'listed_price' => 0, 'customer_name' => 'Expired Customer', 'phone' => '0799000000', 'email' => 'customer@example.test',
            'location' => 'Nairobi', 'preferred_date' => '2026-11-25', 'preferred_time' => '11:00', 'status' => 'Quoted',
            'quote_issued_at' => now()->subDays(30), 'status_history' => [['status' => 'Quoted', 'at' => now()->subDays(30)->toIso8601String()]],
        ]);
        $validQuote = Booking::create([
            'reference' => 'HMC-2026-VALID001', 'service_id' => $service->id, 'package_name' => 'Request a Quote',
            'listed_price' => 0, 'customer_name' => 'Valid Customer', 'phone' => '0799000001', 'location' => 'Nairobi',
            'preferred_date' => '2026-11-25', 'preferred_time' => '12:00', 'status' => 'Quoted', 'quote_issued_at' => now()->subDays(29),
        ]);
        Notification::fake();

        $this->artisan('bookings:expire-quotes')->assertSuccessful()->expectsOutput('Expired 1 quotation(s).');

        $this->assertDatabaseHas('bookings', ['id' => $expiredQuote->id, 'status' => 'Expired']);
        $this->assertDatabaseHas('bookings', ['id' => $validQuote->id, 'status' => 'Quoted']);
        Notification::assertSentOnDemandTimes(BookingUpdate::class, 1);
    }

    public function test_expired_quotes_cannot_be_confirmed_and_can_be_reissued(): void
    {
        $this->travelTo(now('UTC')->setDate(2026, 11, 24)->setTime(10, 0));
        $booking = Booking::create([
            'reference' => 'HMC-2026-REISSUE1', 'service_id' => $this->service()->id, 'package_name' => 'Request a Quote',
            'listed_price' => 0, 'customer_name' => 'Quote Customer', 'phone' => '0799000000', 'location' => 'Nairobi',
            'preferred_date' => '2026-11-25', 'preferred_time' => '11:00', 'status' => 'Quoted', 'quote_issued_at' => now()->subDays(30),
        ]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->patch(route('admin.bookings.update', $booking), ['status' => 'Confirmed'])
            ->assertSessionHasErrors(['status' => 'This quotation has expired. Reissue it as Quoted before confirming the booking.']);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'Quoted']);

        $this->actingAs($admin)->patch(route('admin.bookings.update', $booking), ['status' => 'Quoted'])->assertSessionHasNoErrors();
        $booking->refresh();
        $this->assertSame('Quoted', $booking->status);
        $this->assertSame(now()->format('Y-m-d H:i:s'), $booking->quote_issued_at->format('Y-m-d H:i:s'));
    }

    private function service(): Service
    {
        return Service::create([
            'name' => 'House Deep Cleaning', 'slug' => 'house-deep-cleaning',
            'summary' => 'Cleaning for kitchens, bathrooms, and bedrooms.', 'is_active' => true,
        ]);
    }

    private function payload(Service $service): array
    {
        return [
            'request_token' => (string) Str::uuid(),
            'service_id' => $service->id,
            'customer_name' => 'Example Customer', 'phone' => '0799000000', 'email' => 'customer@example.test',
            'preferred_date' => '2026-11-19', 'preferred_time' => '11:00',
            'location' => 'Nairobi',
        ];
    }
}
