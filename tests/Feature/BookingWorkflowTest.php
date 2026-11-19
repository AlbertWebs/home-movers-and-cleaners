<?php

namespace Tests\Feature;

use App\BookingSchedule;
use App\Models\Booking;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\BookingUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
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
        $booking = Booking::create([
            'reference' => 'HMC-2026-EMAIL01', 'service_id' => $service->id, 'package_name' => 'Request a Quote',
            'listed_price' => 0, 'customer_name' => '<script>alert(1)</script>', 'phone' => '0799000000',
            'location' => 'Nairobi', 'preferred_date' => '2026-11-20', 'preferred_time' => '09:00', 'status' => 'Pending',
        ]);
        $mail = (new BookingUpdate($booking, false, 'https://example.test/booking/status'))->toMail(new AnonymousNotifiable);
        $html = view($mail->view['html'], $mail->data())->render();

        $this->assertStringContainsString('Home Movers and Cleaners', $html);
        $this->assertStringContainsString('HMC-2026-EMAIL01', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
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
            ->assertOk()->assertJsonPath('dates.2026-11-19.0', '09:00');

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->patch(route('admin.bookings.update', 1), ['status' => 'Cancelled'])->assertSessionHasNoErrors();
        $this->getJson(route('booking.availability', ['service_id' => $service->id, 'month' => '2026-11']))
            ->assertOk()->assertJsonPath('dates.2026-11-19.0', '08:00');
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
        $payload = array_replace($settings, ['blocked_dates' => '']);
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->patch(route('admin.bookings.schedule'), $payload)->assertSessionHasNoErrors();
        $this->getJson(route('booking.availability', ['service_id' => $service->id, 'month' => '2026-11']))
            ->assertOk()->assertJsonPath('dates.2026-11-19', []);
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
            'preferred_date' => '2026-11-19', 'preferred_time' => '08:00',
            'location' => 'Nairobi',
        ];
    }
}
