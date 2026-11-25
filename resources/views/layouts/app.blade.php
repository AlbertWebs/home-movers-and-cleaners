<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $seoPage = $service ?? $page ?? $article ?? null;
        $seoRoute = request()->route()?->getName();
        $routeSeoTitle = $seoRoute === 'home' ? ($siteSettings['seo_title'] ?? null) : ($siteSettings['seo_'.$seoRoute.'_title'] ?? null);
        $routeSeoDescription = $seoRoute === 'home' ? ($siteSettings['seo_description'] ?? null) : ($siteSettings['seo_'.$seoRoute.'_description'] ?? null);
        $seoTitle = html_entity_decode(trim($routeSeoTitle ?: $__env->yieldContent('title', $siteSettings['company_name'] ?? 'Home Movers and Cleaners')));
        $titleSuffix = trim($siteSettings['seo_title_suffix'] ?? '| Home Movers and Cleaners');
        if ($titleSuffix !== '' && ! str_contains(mb_strtolower($seoTitle), mb_strtolower(ltrim($titleSuffix, '| ')))) {
            $seoTitle .= ' '.$titleSuffix;
        }
        $seoDescription = html_entity_decode(trim($routeSeoDescription ?: $__env->yieldContent('description', $siteSettings['seo_description'] ?? 'Moving, fumigation and professional home cleaning services in Nairobi.')));
        $robots = trim($__env->yieldContent('robots', $siteSettings['seo_'.$seoRoute.'_robots'] ?? $siteSettings['seo_robots'] ?? 'index, follow'));
        $robots = $seoPage?->seo_robots ?: $robots;
        $canonical = $seoPage?->canonical_url ?: ($siteSettings['seo_'.$seoRoute.'_canonical_url'] ?? url()->current());
        $socialImagePath = $seoPage?->og_image ?: ($seoPage?->image ?? $seoPage?->featured_image ?? $siteSettings['seo_'.$seoRoute.'_og_image'] ?? $siteSettings['social_image'] ?? 'images/moving-hero.png');
        $socialImage = str_starts_with($socialImagePath, 'http') ? $socialImagePath : asset($socialImagePath);
        $ogTitle = $seoPage?->og_title ?: ($siteSettings['seo_'.$seoRoute.'_og_title'] ?? $seoTitle);
        $ogDescription = $seoPage?->og_description ?: ($siteSettings['seo_'.$seoRoute.'_og_description'] ?? $seoDescription);
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="{{ $robots }}">
    @unless(str_contains($robots, 'noindex'))
        <link rel="canonical" href="{{ $canonical }}">
        <meta property="og:type" content="@yield('og-type', 'website')">
        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $socialImage }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $ogTitle }}">
        <meta name="twitter:description" content="{{ $ogDescription }}">
        <meta name="twitter:image" content="{{ $socialImage }}">
    @endunless
    @if($siteSettings['google_site_verification'] ?? null)<meta name="google-site-verification" content="{{ $siteSettings['google_site_verification'] }}">@endif
    @if(request()->routeIs('home', 'contact'))
        @php
            $businessSchema = [
                '@context' => 'https://schema.org',
                '@graph' => [
                    ['@type' => 'Organization', '@id' => route('home').'#organization', 'name' => $siteSettings['company_name'] ?? 'Home Movers and Cleaners', 'url' => route('home'), 'logo' => asset($siteSettings['primary_logo'] ?? 'images/brand-logo.png'), 'telephone' => $siteSettings['phone'] ?? '07999 48 186', 'sameAs' => array_values($siteSettings->only(['facebook', 'instagram', 'tiktok', 'youtube'])->filter()->all())],
                    ['@type' => 'WebSite', '@id' => route('home').'#website', 'url' => route('home'), 'name' => $siteSettings['company_name'] ?? 'Home Movers and Cleaners', 'publisher' => ['@id' => route('home').'#organization'], 'inLanguage' => 'en-KE'],
                    ['@type' => 'LocalBusiness', '@id' => route('home').'#localbusiness', 'name' => $siteSettings['company_name'] ?? 'Home Movers and Cleaners', 'url' => route('home'), 'telephone' => $siteSettings['phone'] ?? '07999 48 186', 'areaServed' => $siteSettings['business_location'] ?? 'Nairobi, Kenya', 'image' => $socialImage, 'sameAs' => array_values($siteSettings->only(['facebook', 'instagram', 'tiktok', 'youtube'])->filter()->all())],
                ],
            ];
        @endphp
        <script type="application/ld+json">@json($businessSchema)</script>
    @endif
    @stack('structured-data')
    <meta name="theme-color" content="#293480">
    @if($siteSettings['favicon'] ?? null)
        <link rel="icon" type="image/png" href="{{ asset($siteSettings['favicon']) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('body-class')">
    <div class="utility-bar" id="page-top" tabindex="-1">
        <div class="wrap utility-inner">
            <div class="utility-meta">
                <a class="utility-location" href="{{ route('contact') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s7-7.2 7-13a7 7 0 1 0-14 0c0 5.8 7 13 7 13zm0-9.5a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7z"/></svg><span>Nairobi, Kenya</span></a>
                <span class="utility-divider" aria-hidden="true"></span>
                <span class="utility-hours"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Mon - Sat: 8:00 AM - 6:00 PM</span>
            </div>
            <span class="utility-tagline">{{ $siteSettings['tagline'] ?? 'Wherever You Go, We Take Home With You.' }}<i class="utility-tagline-underline" aria-hidden="true"></i></span>
            <div class="utility-links">
                @if($siteSettings['facebook'] ?? null)<a class="utility-social" href="{{ $siteSettings['facebook'] }}" aria-label="Facebook"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 21v-7.2h2.4l.4-2.8h-2.8V9.2c0-.8.2-1.4 1.4-1.4h1.5V5.3c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8V11H8v2.8h2.5V21z"/></svg></a>@endif
                @if($siteSettings['instagram'] ?? null)<a class="utility-social" href="{{ $siteSettings['instagram'] }}" aria-label="Instagram"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.2" y="3.2" width="17.6" height="17.6" rx="5"/><circle cx="12" cy="12" r="4.1"/><circle class="icon-fill" cx="17.7" cy="6.5" r="1.2"/></svg></a>@endif
                @if($siteSettings['tiktok'] ?? null)<a class="utility-social" href="{{ $siteSettings['tiktok'] }}" aria-label="TikTok"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16.2 3c.3 2.1 1.5 3.7 3.8 4.1v3.2a8.1 8.1 0 0 1-3.8-1.2v6.1a6.2 6.2 0 1 1-6.2-6.2c.4 0 .8 0 1.2.1v3.4a2.9 2.9 0 1 0 1.8 2.7V3z"/></svg></a>@endif
                <span class="utility-divider" aria-hidden="true"></span>
                @if($whatsappLink('Hello, I found your website and would like to learn more about your services.'))<a class="utility-whatsapp" href="{{ $whatsappLink('Hello, I found your website and would like to learn more about your services.') }}" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 11.8a8.1 8.1 0 0 1-11.9 7.1L4 20l1.1-4.1a8.1 8.1 0 1 1 15.1-4.1zm-8.1-6.2a6.2 6.2 0 0 0-5.3 9.4l.3.5-.6 2.1 2.2-.6.5.3a6.2 6.2 0 1 0 2.9-11.7zm3.4 8.8c-.2-.1-1.2-.6-1.4-.7s-.3-.1-.4.1-.5.7-.7.8-.2.2-.4.1a5.1 5.1 0 0 1-1.7-1 6.2 6.2 0 0 1-1.2-1.5c-.1-.2 0-.3.1-.4l.3-.4c.1-.1.1-.2.2-.3s0-.2 0-.3-.4-1-.6-1.3-.3-.3-.4-.3h-.4c-.1 0-.3.1-.5.2s-.7.7-.7 1.6.7 1.9.8 2c.1.1 1.4 2.2 3.5 3 .5.2.8.3 1.1.3.5.1.9.1 1.2.1.4-.1 1.2-.5 1.4-1s.2-.9.2-1-.2-.1-.4-.2z"/></svg> WhatsApp Us</a>@endif
            </div>
        </div>
    </div>
    <header class="site-header">
        <div class="wrap nav-row">
            <a href="{{ route('home') }}" class="brand" aria-label="{{ $siteSettings['company_name'] ?? 'Home Movers and Cleaners' }} home">
                <img src="{{ asset($siteSettings['primary_logo'] ?? 'images/brand-logo.png') }}" alt="Home Movers and Cleaners logo">
            </a>

            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Toggle site navigation">
                <span class="menu-toggle-label">Menu</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/></svg>
            </button>

            <nav id="site-nav" class="main-nav" aria-label="Primary navigation">
                <div class="main-nav-links">
                    <a class="{{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">Home</a>
                    <a class="{{ request()->routeIs('about') ? 'is-active' : '' }}" href="{{ route('about') }}">About Us</a>
                    <a class="{{ request()->routeIs('services', 'service') ? 'is-active' : '' }}" href="{{ route('services') }}">Services <span class="nav-chevron" aria-hidden="true"><svg viewBox="0 0 16 16"><path d="m3 6 5 5 5-5"/></svg></span></a>
                    <a class="{{ request()->routeIs('pricing') ? 'is-active' : '' }}" href="{{ route('pricing') }}">Pricing</a>
                    <a class="{{ request()->routeIs('contact') ? 'is-active' : '' }}" href="{{ route('contact') }}">Contact</a>
                </div>
                <a class="nav-phone" href="tel:{{ preg_replace('/\s+/', '', $siteSettings['phone'] ?? '0799948186') }}">
                    <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6.6 2.8 9.4 2c.7-.2 1.4.2 1.6.9l1.1 3.4c.2.6 0 1.2-.5 1.6L9.8 9a14 14 0 0 0 5.2 5.2l1.1-1.8c.3-.5 1-.7 1.6-.5l3.4 1.1c.7.2 1.1.9.9 1.6l-.8 2.8c-.2.7-.8 1.1-1.5 1.1A17.2 17.2 0 0 1 5.5 4.3c0-.7.4-1.3 1.1-1.5z"/></svg></span>{{ $siteSettings['phone'] ?? '07999 48 186' }}
                </a>
                <a class="button button-small" href="{{ route('booking') }}"><span class="nav-booking-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 10h18m-13 4h2m4 0h2m-8 4h2m4 0h2"/></svg></span>Book a Service<span class="nav-booking-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 12h15m-6-6 6 6-6 6"/></svg></span></a>
            </nav>
        </div>
    </header>

    <main>
        @if(session('success'))
            <div class="wrap"><div class="notice">{{ session('success') }}</div></div>
        @endif
        @yield('content')
    </main>

    <footer class="site-footer footer-inspired">
        <div class="footer-wave" aria-hidden="true"><img src="{{ asset('images/footer-wave.svg') }}" alt=""></div>
        @if(($siteSettings['footer_house_watermark'] ?? '1') === '1')
            <img class="footer-house-watermark" src="{{ asset('images/house-watermark.svg') }}" alt="" aria-hidden="true">
        @endif
        <div class="wrap footer-grid">
            <div class="footer-brand-column">
                <a class="footer-brand" href="{{ route('home') }}" aria-label="{{ $siteSettings['company_name'] ?? 'Home Movers and Cleaners' }} home">
                    <img src="{{ asset($siteSettings['footer_logo'] ?? 'images/brand-logo.png') }}" alt="">
                    <span><strong>HOME MOVERS</strong><small>AND CLEANERS</small></span>
                </a>
                <p>{{ $siteSettings['footer_description'] ?? 'Professional moving, fumigation, sofa and mattress cleaning, and house deep cleaning for homes and businesses.' }}</p>
                <div class="footer-socials" aria-label="Social media">
                    @foreach(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'youtube' => 'YouTube'] as $network => $label)
                        @if(!empty($siteSettings[$network] ?? null))
                            <a href="{{ $siteSettings[$network] }}" aria-label="{{ $label }}"><img src="{{ asset('images/social-'.$network.'.svg') }}" alt=""></a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="footer-column">
                <h3>Quick Links</h3>
                @php($footerRouteNames = ['home' => 'home', 'about' => 'about', 'services' => 'services', 'pricing' => 'pricing', 'booking' => 'booking', 'contact' => 'contact'])
                @for($index = 1; $index <= 6; $index++)
                    @php($footerRoute = $siteSettings['footer_link_'.$index.'_route'] ?? array_values($footerRouteNames)[$index - 1])
                    <a href="{{ route($footerRouteNames[$footerRoute] ?? 'home') }}"><span aria-hidden="true">›</span>{{ $siteSettings['footer_link_'.$index.'_label'] ?? ['Home', 'About Us', 'Services', 'Moving Charges', 'Book a Service', 'Contact Us'][$index - 1] }}</a>
                @endfor
                <a href="{{ route('gallery') }}"><span aria-hidden="true">›</span>Gallery</a>
                <a href="{{ route('blog') }}"><span aria-hidden="true">›</span>Resources</a>
            </div>
            <div class="footer-column footer-service-column">
                <h3>Our Services</h3>
                @foreach($footerServices as $service)
                    @php($serviceIcon = str_contains($service->slug, 'moving') ? 'service-moving.svg' : (str_contains($service->slug, 'fumigation') ? 'service-fumigation.svg' : (str_contains($service->slug, 'sofa') || str_contains($service->slug, 'mattress') ? 'service-sofa-mattress.svg' : 'service-deep-cleaning.svg')))
                    <a href="{{ route('service', $service->slug) }}"><img src="{{ asset('images/'.$serviceIcon) }}" alt="">{{ $service->name }}</a>
                @endforeach
            </div>
            <div class="footer-column footer-contact-column">
                <h3>Get in Touch</h3>
                <a class="footer-contact-item" href="tel:{{ preg_replace('/\s+/', '', $siteSettings['phone'] ?? '0799948186') }}"><span><img src="{{ asset('images/contact-phone.svg') }}" alt=""></span>{{ $siteSettings['phone'] ?? '07999 48 186' }}</a>
                @if(!empty($siteSettings['business_email'] ?? null))
                    <a class="footer-contact-item" href="mailto:{{ $siteSettings['business_email'] }}"><span><img src="{{ asset('images/contact-email.svg') }}" alt=""></span>{{ $siteSettings['business_email'] }}</a>
                @endif
                <a class="footer-contact-item" href="{{ route('contact') }}"><span><img src="{{ asset('images/contact-location.svg') }}" alt=""></span>{{ $siteSettings['business_location'] ?? 'Nairobi, Kenya' }}</a>
                @if($whatsappLink('Hello, I found your website and would like to enquire about your services.'))<a class="footer-whatsapp" href="{{ $whatsappLink('Hello, I found your website and would like to enquire about your services.') }}" target="_blank" rel="noopener noreferrer"><img src="{{ asset('images/whatsapp-symbol.svg') }}" alt="">Chat on WhatsApp</a>@endif
            </div>
        </div>
        <div class="wrap footer-bottom">
            <span>© {{ date('Y') }} {{ $siteSettings['company_name'] ?? 'HOME MOVERS AND CLEANERS' }}. All Rights Reserved.</span>
            <div>@foreach($legalPages as $policy)<a href="{{ in_array($policy->slug, ['privacy', 'terms'], true) ? route($policy->slug) : route('content.show', $policy->slug) }}">{{ $policy->title }}</a>@endforeach<a href="{{ route('sitemap') }}">Sitemap</a>@if(($siteSettings['ga4_measurement_id'] ?? null) || ($siteSettings['gtm_container_id'] ?? null))<button class="footer-cookie-settings" type="button" data-analytics-open>Cookie settings</button>@endif</div>
        </div>
        <div class="footer-credit">Powered by <a href="https://designekta.com" target="_blank" rel="noopener noreferrer">Designekta Studios</a></div>
    </footer>
    <a class="back-to-top" href="#page-top" aria-label="Back to top" hidden>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 11 6-6 6 6m-6-6v14"/></svg>
    </a>
    @if(($siteSettings['ga4_measurement_id'] ?? null) || ($siteSettings['gtm_container_id'] ?? null))
        <div class="analytics-consent" data-analytics-consent role="dialog" aria-label="Analytics preferences" hidden>
            <p>Allow analytics cookies to help us understand how visitors use this website. No booking or contact details are shared.</p>
            <button type="button" data-analytics-choice="accept">Allow analytics</button>
            <button type="button" data-analytics-choice="reject">Essential only</button>
        </div>
        <script>
            window.siteAnalyticsConfig = @json(['ga4' => $siteSettings['ga4_measurement_id'] ?? null, 'gtm' => $siteSettings['gtm_container_id'] ?? null]);
        </script>
    @endif
    @stack('page-scripts')
</body>
</html>
