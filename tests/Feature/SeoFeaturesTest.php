<?php

namespace Tests\Feature;

use App\Models\BlogArticle;
use App\Models\UrlRedirect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_articles_are_indexable_and_drafts_are_private(): void
    {
        $draft = BlogArticle::factory()->create(['slug' => 'private-moving-notes']);
        $article = BlogArticle::factory()->published()->create(['slug' => 'moving-day-checklist']);

        $this->get(route('blog'))->assertOk()->assertSee($article->title)->assertDontSee($draft->title);
        $this->get(route('blog.show', $article->slug))->assertOk()->assertSee($article->title)->assertSee('application/ld+json', false);
        $this->get(route('blog.show', $draft->slug))->assertNotFound();
        $this->get(route('sitemap'))->assertSee(route('blog.show', $article->slug), false)->assertDontSee(route('blog.show', $draft->slug), false);
    }

    public function test_redirect_manager_requires_administrator_access_and_redirects_unknown_paths(): void
    {
        $this->get(route('admin.seo'))->assertRedirect(route('admin.login'));
        $administrator = User::factory()->create(['is_admin' => true]);
        $this->actingAs($administrator)->post(route('admin.seo.redirects.store'), [
            'from_path' => '/old-service-address',
            'destination' => '/about',
            'status_code' => 301,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $redirect = UrlRedirect::query()->firstOrFail();
        $this->assertSame('/old-service-address', $redirect->from_path);
        $this->get('/old-service-address')->assertStatus(301)->assertRedirect('/about');
    }

    public function test_redirect_manager_rejects_a_redirect_loop_and_chains(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);
        UrlRedirect::query()->create(['from_path' => '/old-one', 'destination' => '/old-two', 'status_code' => 301, 'is_active' => true]);

        $this->actingAs($administrator)->post(route('admin.seo.redirects.store'), [
            'from_path' => '/old-two',
            'destination' => '/old-one',
            'status_code' => 301,
        ])->assertSessionHasErrors(['destination']);
        $this->assertDatabaseCount('url_redirects', 1);
    }

    public function test_robots_file_leaves_signed_booking_pages_crawlable_for_noindex_directives(): void
    {
        $this->get(route('robots'))->assertOk()->assertSee('Disallow: /admin')->assertDontSee('Disallow: /booking/')->assertSee(route('sitemap'), false);
    }
}
