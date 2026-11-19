<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_cta_and_footer_settings(): void
    {
        Storage::fake('public');
        $administrator = User::factory()->create(['is_admin' => true]);
        $payload = [
            'company_name' => 'Home Movers and Cleaners',
            'tagline' => 'Wherever you go, we take home with you.',
            'hero_heading' => 'Professional Moving and Cleaning Services',
            'hero_description' => 'A helpful home service team.',
            'about_preview' => 'Making home services easier.',
            'phone' => '07999 48 186',
            'whatsapp' => '254799948186',
            'business_email' => 'hello@example.test',
            'business_location' => 'Nairobi, Kenya',
            'footer_description' => 'Professional moving and cleaning for local homes.',
            'footer_house_watermark' => '1',
            'cta_eyebrow' => 'A FRESH START CAN BE SIMPLE',
            'cta_heading' => 'Ready to move or refresh your space?',
            'cta_description' => 'Our team is here to help.',
            'cta_booking_label' => 'Book a Service',
            'cta_booking_destination' => 'booking',
            'cta_whatsapp_label' => 'Chat on WhatsApp',
            'cta_whatsapp_destination' => 'whatsapp',
            'footer_logo_file' => UploadedFile::fake()->image('footer-logo.png'),
            'facebook' => '',
            'instagram' => '',
            'tiktok' => '',
            'youtube' => '',
        ];

        foreach (['home', 'about', 'services', 'pricing', 'booking', 'contact'] as $index => $route) {
            $number = $index + 1;
            $payload["footer_link_{$number}_label"] = 'Link '.$number;
            $payload["footer_link_{$number}_route"] = $route;
        }

        $response = $this->actingAs($administrator)->put(route('admin.settings.update'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('site_settings', ['key' => 'cta_heading', 'value' => 'Ready to move or refresh your space?']);
        $this->assertDatabaseHas('site_settings', ['key' => 'business_email', 'value' => 'hello@example.test']);
        $this->assertDatabaseHas('site_settings', ['key' => 'footer_link_6_route', 'value' => 'contact']);
        $logoPath = SiteSetting::query()->where('key', 'footer_logo')->value('value');
        $this->assertStringStartsWith('storage/site/', $logoPath);
        Storage::disk('public')->assertExists(substr($logoPath, strlen('storage/')));
    }
}
