<?php

namespace App\Providers;

use App\Models\ContentPage;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\WhatsAppLink;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-admin', fn (User $user): bool => $user->hasAdminRole('super_admin', 'administrator', 'booking_manager', 'content_editor'));
        Gate::define('manage-bookings', fn (User $user): bool => $user->hasAdminRole('super_admin', 'administrator', 'booking_manager'));
        Gate::define('manage-content', fn (User $user): bool => $user->hasAdminRole('super_admin', 'administrator', 'content_editor'));
        Gate::define('manage-settings', fn (User $user): bool => $user->hasAdminRole('super_admin', 'administrator'));
        View::composer('*', function ($view): void {
            $settings = SiteSetting::query()->pluck('value', 'key');
            $view->with([
                'siteSettings' => $settings,
                'whatsappLink' => fn (string $message): ?string => WhatsAppLink::make($settings->get('whatsapp'), $message),
            ]);
        });
        View::composer('layouts.app', function ($view): void {
            $view->with('footerServices', Service::query()->where('is_active', true)->orderBy('sort_order')->get());
            $legalPages = collect(config('site_content.pages'))
                ->except('about')
                ->map(function (array $defaults, string $slug): ContentPage {
                    return ContentPage::query()->where('slug', $slug)->first()
                        ?? new ContentPage($defaults + ['slug' => $slug, 'is_published' => false]);
                })
                ->values();
            $view->with('legalPages', $legalPages);
        });
    }
}
