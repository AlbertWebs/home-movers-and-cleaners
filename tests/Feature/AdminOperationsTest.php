<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_calendar_and_csv_export_respect_the_selected_period_and_filters(): void
    {
        $service = $this->service();
        $this->booking($service, 'HMC-2026-000101', 'Calendar Customer', '2026-11-20');
        $this->booking($service, 'HMC-2026-000102', 'Other Customer', '2026-12-20');
        $manager = User::factory()->create(['role' => 'booking_manager']);

        $this->actingAs($manager)->get(route('admin.bookings', ['period' => 'month', 'anchor' => '2026-11-01']))
            ->assertOk()->assertSee('November 2026')->assertSee('Calendar Customer')->assertDontSee('Other Customer');
        $export = $this->actingAs($manager)->get(route('admin.bookings.export', ['search' => 'Calendar Customer']))
            ->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8')->assertDownload();
        $this->assertStringContainsString('HMC-2026-000101', $export->streamedContent());
        $this->assertStringNotContainsString('HMC-2026-000102', $export->streamedContent());
    }

    public function test_admin_dashboard_shows_real_booking_metrics_and_upcoming_appointments(): void
    {
        $service = $this->service();
        $booking = $this->booking($service, 'HMC-2026-000103', 'Upcoming Customer', now('Africa/Nairobi')->addDays(3)->toDateString());
        $booking->update(['status' => 'Confirmed']);
        $administrator = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)->get(route('admin.dashboard', ['range' => '90']))
            ->assertOk()->assertSee('Upcoming Customer')->assertSee('Booking trends')->assertSee('Bookings by service')
            ->assertSee('Upcoming appointments')->assertSee('Last 90 days');
    }

    public function test_staff_roles_can_access_only_their_administration_areas(): void
    {
        $bookingManager = User::factory()->create(['role' => 'booking_manager']);
        $contentEditor = User::factory()->create(['role' => 'content_editor']);

        $this->actingAs($bookingManager)->get(route('admin.bookings'))->assertOk();
        $this->actingAs($bookingManager)->get(route('admin.settings'))->assertForbidden();
        $this->actingAs($contentEditor)->get(route('admin.homepage'))->assertOk();
        $this->actingAs($contentEditor)->get(route('admin.bookings'))->assertForbidden();
        $this->actingAs($contentEditor)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_content_editor_can_publish_genuine_customer_feedback_on_the_homepage(): void
    {
        $editor = User::factory()->create(['role' => 'content_editor']);
        $payload = [
            'customer_name' => 'A Nairobi customer', 'service' => 'Moving',
            'quote' => 'The team handled our furniture with care.', 'sort_order' => 1, 'consent_confirmed' => '1',
        ];

        $this->actingAs($editor)->post(route('admin.testimonials.store'), $payload)->assertRedirect();
        $testimonial = Testimonial::query()->firstOrFail();
        $this->assertNotNull($testimonial->consent_confirmed_at);
        $this->get(route('home'))->assertOk()->assertDontSee($payload['quote']);
        $this->actingAs($editor)->patch(route('admin.testimonials.update', $testimonial), $payload + ['is_published' => '1'])->assertRedirect();
        $this->get(route('home'))->assertOk()->assertSee($payload['quote'])->assertSee($payload['customer_name']);
    }

    public function test_enquiries_can_be_searched_updated_and_exported_without_marking_them_read_as_a_side_effect(): void
    {
        $enquiry = Enquiry::create([
            'name' => 'Customer Example', 'phone' => '0799000000', 'email' => 'customer@example.test',
            'subject' => 'Cleaning availability', 'message' => 'Can I request a date?', 'status' => 'new',
        ]);
        $manager = User::factory()->create(['role' => 'booking_manager']);

        $this->actingAs($manager)->get(route('admin.enquiries', ['search' => 'Customer Example']))
            ->assertOk()->assertSee('Customer Example');
        $this->assertDatabaseHas('enquiries', ['id' => $enquiry->id, 'is_read' => false]);
        $this->actingAs($manager)->patch(route('admin.enquiries.update', $enquiry), [
            'status' => 'in_progress', 'internal_notes' => 'Call back in the afternoon.', 'is_read' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('enquiries', ['id' => $enquiry->id, 'status' => 'in_progress', 'is_read' => true]);
        $export = $this->actingAs($manager)->get(route('admin.enquiries.export', ['status' => 'in_progress']))
            ->assertOk()->assertDownload();
        $this->assertStringContainsString('Customer Example', $export->streamedContent());
    }

    private function service(): Service
    {
        return Service::create(['name' => 'Moving', 'slug' => 'moving-services', 'summary' => 'Careful home moves.', 'is_active' => true]);
    }

    private function booking(Service $service, string $reference, string $name, string $date): Booking
    {
        return Booking::create([
            'reference' => $reference, 'service_id' => $service->id, 'package_name' => 'Request a quote',
            'listed_price' => 0, 'customer_name' => $name, 'phone' => '0799000000', 'location' => 'Nairobi',
            'preferred_date' => $date, 'preferred_time' => '09:00', 'status' => 'Pending',
        ]);
    }
}
