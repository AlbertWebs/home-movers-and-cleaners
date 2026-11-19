<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_includes_public_pages_and_active_services(): void
    {
        Service::query()->create([
            'name' => 'Moving Services',
            'slug' => 'moving-services',
            'summary' => 'Careful home moves.',
            'description' => 'Professional moving support.',
            'icon' => 'Moving',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee(route('home'), false);
        $response->assertSee(route('service', 'moving-services'), false);
    }

    public function test_static_page_metadata_controls_canonical_urls_and_sitemap_visibility(): void
    {
        SiteSetting::query()->create(['key' => 'seo_services_title', 'value' => 'Services for Nairobi Homes']);
        SiteSetting::query()->create(['key' => 'seo_services_canonical_url', 'value' => 'https://example.test/our-services']);
        SiteSetting::query()->create(['key' => 'seo_gallery_robots', 'value' => 'noindex, follow']);

        $this->get(route('services'))->assertOk()
            ->assertSee('<title>Services for Nairobi Homes | Home Movers and Cleaners</title>', false)
            ->assertSee('<link rel="canonical" href="https://example.test/our-services">', false);
        $this->get(route('sitemap'))->assertOk()
            ->assertSee('https://example.test/our-services', false)
            ->assertDontSee(route('gallery'), false);
    }
}
