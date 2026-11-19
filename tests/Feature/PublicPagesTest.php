<?php

namespace Tests\Feature;

use App\Models\ContentPage;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_load_with_unique_titles_and_a_single_primary_heading(): void
    {
        $service = Service::create([
            'name' => 'Moving Services', 'slug' => 'moving-services', 'summary' => 'Moving support for Nairobi homes.',
            'description' => 'Request help with your home move.', 'is_active' => true,
        ]);
        $this->seed();
        $routes = [
            route('home'), route('about'), route('services'), route('service', $service->slug),
            route('pricing'), route('gallery'), route('booking'), route('contact'), route('privacy'), route('terms'),
            route('content.show', 'cookies'), route('content.show', 'booking-cancellation'), route('content.show', 'refunds'),
        ];

        $titles = [];
        foreach ($routes as $url) {
            $response = $this->get($url)->assertOk()->assertSee('<h1', false)->assertSee('<title>', false)
                ->assertDontSee('Cleaniners')->assertDontSee('CLEANINERS');
            preg_match('/<title>(.*?)<\/title>/s', $response->getContent(), $titleMatch);
            preg_match_all('/<h1\b/', $response->getContent(), $headingMatches);
            $this->assertCount(1, $headingMatches[0], $url.' should have one primary heading.');
            $this->assertNotEmpty($titleMatch[1] ?? null, $url.' should have a page title.');
            $titles[] = trim(strip_tags($titleMatch[1]));
        }
        $this->assertCount(count(array_unique($titles)), $titles, 'Public page titles should be unique.');
    }

    public function test_unapproved_legal_pages_are_marked_as_drafts_and_excluded_from_the_sitemap(): void
    {
        $this->get(route('privacy'))->assertOk()->assertSee('Draft for review')->assertSee('noindex, follow');
        $this->get(route('terms'))->assertOk()->assertSee('Draft for review')->assertSee('noindex, follow');
        foreach (['cookies', 'booking-cancellation', 'refunds'] as $slug) {
            $this->get(route('content.show', $slug))->assertOk()->assertSee('Draft for review')->assertSee('noindex, follow');
        }
        $this->get(route('sitemap'))->assertOk()->assertDontSee(route('privacy'), false)->assertDontSee(route('terms'), false);

        ContentPage::create([
            'slug' => 'privacy', 'title' => 'Approved Privacy Notice', 'summary' => 'Approved notice.',
            'body' => '## Approved content', 'is_published' => true,
        ]);
        $this->get(route('privacy'))->assertOk()->assertSee('Approved Privacy Notice')->assertDontSee('Draft for review');
        $this->get(route('sitemap'))->assertOk()->assertSee(route('privacy'), false);
    }

    public function test_contact_displays_configured_operating_hours_and_omits_unconfigured_social_links(): void
    {
        SiteSetting::create(['key' => 'operating_hours', 'value' => 'Monday to Saturday, 8:00 to 17:00']);
        SiteSetting::create(['key' => 'facebook', 'value' => '']);

        $this->get(route('contact'))->assertOk()->assertSee('OPERATING HOURS')->assertSee('Monday to Saturday, 8:00 to 17:00')->assertDontSee('aria-label="Facebook"', false);
    }
}
