<?php

use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContentPageController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/about', [ContentPageController::class, 'show'])->defaults('slug', 'about')->name('about');
Route::get('/services', [PublicSiteController::class, 'services'])->name('services');
Route::get('/services/{slug}', [PublicSiteController::class, 'service'])->name('service');
Route::get('/pricing', [PublicSiteController::class, 'pricing'])->name('pricing');
Route::get('/gallery', [PublicSiteController::class, 'gallery'])->name('gallery');
Route::get('/book', [PublicSiteController::class, 'booking'])->name('booking');
Route::post('/book', [BookingController::class, 'store'])->middleware('throttle:6,1')->name('booking.store');
Route::get('/book/availability', [BookingController::class, 'availability'])->middleware('throttle:60,1')->name('booking.availability');
Route::get('/booking/{reference}', [BookingController::class, 'confirmation'])->middleware('signed')->name('booking.confirmation');
Route::get('/contact', [PublicSiteController::class, 'contact'])->name('contact');
Route::get('/blog', [PublicSiteController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('blog.show');
Route::post('/contact', [BookingController::class, 'enquiry'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/privacy', [ContentPageController::class, 'show'])->defaults('slug', 'privacy')->name('privacy');
Route::get('/terms', [ContentPageController::class, 'show'])->defaults('slug', 'terms')->name('terms');
Route::get('/pages/{slug}', [ContentPageController::class, 'show'])->where('slug', '(?!(?:about|privacy|terms)$)[a-z0-9-]+')->name('content.show');
Route::get('/sitemap.xml', [PublicSiteController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PublicSiteController::class, 'robots'])->name('robots');
Route::get('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'authenticate'])->middleware('throttle:5,1')->name('admin.authenticate');
Route::middleware(['auth', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    Route::get('/', [AdminController::class, 'dashboard'])->middleware('can:manage-settings')->name('dashboard');
    Route::middleware('can:manage-bookings')->group(function (): void {
        Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
        Route::get('/bookings/export', [AdminController::class, 'exportBookings'])->name('bookings.export');
        Route::get('/bookings/{booking}/photos/{photo}', [BookingController::class, 'quotePhoto'])->whereNumber('photo')->name('bookings.photos.show');
        Route::patch('/bookings/schedule', [AdminController::class, 'updateSchedule'])->name('bookings.schedule');
        Route::patch('/bookings/{booking}', [AdminController::class, 'updateBooking'])->name('bookings.update');
        Route::get('/enquiries', [AdminController::class, 'enquiries'])->name('enquiries');
        Route::get('/enquiries/export', [AdminController::class, 'exportEnquiries'])->name('enquiries.export');
        Route::patch('/enquiries/{enquiry}', [AdminController::class, 'updateEnquiry'])->name('enquiries.update');
    });
    Route::middleware('can:manage-content')->group(function (): void {
        Route::get('/blog', [BlogController::class, 'adminIndex'])->name('blog.index');
        Route::post('/blog', [BlogController::class, 'store'])->name('blog.store');
        Route::patch('/blog/{article}', [BlogController::class, 'update'])->name('blog.update');
        Route::delete('/blog/{article}', [BlogController::class, 'destroy'])->name('blog.destroy');
        Route::get('/pages', [ContentPageController::class, 'index'])->name('pages');
        Route::post('/pages', [ContentPageController::class, 'save'])->name('pages.save');
        Route::get('/pages/{page}/preview', [ContentPageController::class, 'preview'])->name('pages.preview');
        Route::get('/pricing', [AdminController::class, 'pricing'])->name('pricing');
        Route::post('/pricing/{service}', [AdminController::class, 'createPrice'])->name('pricing.store');
        Route::patch('/pricing/{package}', [AdminController::class, 'updatePrice'])->name('pricing.update');
        Route::get('/services', [AdminController::class, 'services'])->name('services');
        Route::post('/services', [AdminController::class, 'createService'])->name('services.store');
        Route::patch('/services/{service}', [AdminController::class, 'updateService'])->name('services.update');
        Route::get('/gallery', [AdminController::class, 'gallery'])->name('gallery');
        Route::post('/gallery', [AdminController::class, 'uploadGalleryItem'])->name('gallery.store');
        Route::patch('/gallery/{item}', [AdminController::class, 'updateGalleryItem'])->name('gallery.update');
        Route::delete('/gallery/{item}', [AdminController::class, 'deleteGalleryItem'])->name('gallery.delete');
        Route::get('/media', [AdminController::class, 'media'])->name('media');
        Route::post('/media', [AdminController::class, 'uploadMedia'])->name('media.store');
        Route::patch('/media/{asset}', [AdminController::class, 'updateMedia'])->name('media.update');
        Route::delete('/media/{asset}', [AdminController::class, 'deleteMedia'])->name('media.delete');
        Route::get('/testimonials', [AdminController::class, 'testimonials'])->name('testimonials');
        Route::post('/testimonials', [AdminController::class, 'createTestimonial'])->name('testimonials.store');
        Route::patch('/testimonials/{testimonial}', [AdminController::class, 'updateTestimonial'])->name('testimonials.update');
        Route::delete('/testimonials/{testimonial}', [AdminController::class, 'deleteTestimonial'])->name('testimonials.delete');
        Route::get('/homepage', [AdminController::class, 'homepageSettings'])->name('homepage');
        Route::put('/homepage', [AdminController::class, 'updateHomepage'])->name('homepage.update');
    });
    Route::middleware('can:manage-settings')->group(function (): void {
        Route::get('/seo', [SeoController::class, 'index'])->name('seo');
        Route::post('/seo/redirects', [SeoController::class, 'storeRedirect'])->name('seo.redirects.store');
        Route::patch('/seo/redirects/{redirect}', [SeoController::class, 'updateRedirect'])->name('seo.redirects.update');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::get('/staff', [AdminController::class, 'staff'])->name('staff');
        Route::patch('/staff/{user}', [AdminController::class, 'updateStaffRole'])->name('staff.update');
    });
});

Route::fallback([SeoController::class, 'redirectLegacy']);
