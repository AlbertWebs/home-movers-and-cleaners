<?php

namespace App\Http\Controllers;

use App\BookingSchedule;
use App\Models\BlogArticle;
use App\Models\ContentPage;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'services' => Service::with('packages')->where('is_active', true)->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::query()->where('is_published', true)->orderBy('sort_order')->latest()->get(),
        ]);
    }

    public function gallery(): View
    {
        return view('pages.gallery', [
            'featuredPhotos' => config('site_content.project_photos', []),
            'galleryItems' => GalleryItem::where('is_published', true)->orderBy('sort_order')->latest()->get(),
        ]);
    }

    public function services(): View
    {
        return view('pages.services', ['services' => Service::with('packages')->where('is_active', true)->orderBy('sort_order')->get()]);
    }

    public function service(string $slug): View
    {
        $service = Service::with('packages')->where('is_active', true)->where('slug', $slug)->firstOrFail();

        return view('pages.service', compact('service'));
    }

    public function pricing(): View
    {
        return view('pages.pricing', ['services' => Service::with(['packages' => fn ($query) => $query->where('is_active', true)])->where('is_active', true)->orderBy('sort_order')->get()]);
    }

    public function booking(BookingSchedule $schedule): View
    {
        $settings = $schedule->settings();
        $configuredHours = is_array($settings['hours'] ?? null) ? $settings['hours'] : [];
        $workingHours = collect(BookingSchedule::HOURS)->only($configuredHours)->all();

        return view('pages.booking', [
            'services' => Service::with(['packages' => fn ($query) => $query->where('is_active', true)])->where('is_active', true)->orderBy('sort_order')->get(),
            'workingHours' => $workingHours,
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', ['services' => Service::where('is_active', true)->orderBy('sort_order')->get()]);
    }

    public function blog(): View
    {
        return view('pages.blog', ['articles' => BlogArticle::query()->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now())->latest('published_at')->paginate(9)]);
    }

    public function sitemap(): Response
    {
        $settings = SiteSetting::query()->pluck('value', 'key');
        $siteRobots = $settings->get('seo_robots');
        $urls = collect();
        if (str_contains($siteRobots ?? 'index, follow', 'noindex')) {
            return response()->view('pages.sitemap', ['urls' => $urls], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
        }

        $urls = collect(['home', 'services', 'pricing', 'gallery', 'booking', 'contact'])
            ->reject(fn (string $name): bool => str_contains($settings->get('seo_'.$name.'_robots', ''), 'noindex'))
            ->map(fn (string $name): array => ['loc' => $settings->get('seo_'.$name.'_canonical_url') ?: route($name), 'lastmod' => null]);

        $aboutPage = ContentPage::query()->where('slug', 'about')->where('is_published', true)->first(['canonical_url', 'seo_robots']);
        if (! str_contains($aboutPage?->seo_robots ?? '', 'noindex')) {
            $urls->push(['loc' => $aboutPage?->canonical_url ?: route('about'), 'lastmod' => null]);
        }

        Service::query()->where('is_active', true)->where(function ($query): void {
            $query->whereNull('seo_robots')->orWhere('seo_robots', 'not like', '%noindex%');
        })->get(['slug', 'canonical_url', 'updated_at'])->each(function (Service $service) use ($urls): void {
            $urls->push(['loc' => $service->canonical_url ?: route('service', $service->slug), 'lastmod' => $service->updated_at?->toDateString()]);
        });
        ContentPage::query()->where('is_published', true)->where('slug', '!=', 'about')->where(function ($query): void {
            $query->whereNull('seo_robots')->orWhere('seo_robots', 'not like', '%noindex%');
        })->get(['slug', 'canonical_url', 'updated_at'])->each(function (ContentPage $page) use ($urls): void {
            $urls->push(['loc' => $page->canonical_url ?: match ($page->slug) {
                'privacy' => route('privacy'),
                'terms' => route('terms'),
                default => route('content.show', $page->slug),
            }, 'lastmod' => $page->updated_at?->toDateString()]);
        });
        $publishedArticles = BlogArticle::query()->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now())->where(function ($query): void {
            $query->whereNull('seo_robots')->orWhere('seo_robots', 'not like', '%noindex%');
        })->get(['slug', 'canonical_url', 'updated_at']);
        if ($publishedArticles->isNotEmpty()) {
            if (! str_contains($settings->get('seo_blog_robots', ''), 'noindex')) {
                $urls->push(['loc' => $settings->get('seo_blog_canonical_url') ?: route('blog'), 'lastmod' => $publishedArticles->max('updated_at')?->toDateString()]);
            }
        }
        $publishedArticles->each(function (BlogArticle $article) use ($urls): void {
            $urls->push(['loc' => $article->canonical_url ?: route('blog.show', $article->slug), 'lastmod' => $article->updated_at?->toDateString()]);
        });

        return response()->view('pages.sitemap', ['urls' => $urls->unique('loc')->values()], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        return response("User-agent: *\nDisallow: /admin\nSitemap: ".route('sitemap')."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
