@extends('layouts.app')
@section('body-class', 'home-page')
@section('title', $siteSettings['seo_title'] ?? (($siteSettings['company_name'] ?? 'Home Movers and Cleaners').' | Moving & Cleaning in Nairobi'))
@section('description', $siteSettings['seo_description'] ?? 'Book moving, fumigation, sofa and mattress cleaning, and house deep cleaning in Nairobi. Explore services and starting prices.')
@section('content')
@php
    $homeSettings = json_decode($siteSettings['homepage_content'] ?? '', true) ?: [];
    $homeCopy = $homeSettings['copy'] ?? [];
    $homeImages = $homeSettings['images'] ?? [];
@endphp
@foreach($homeSettings['order'] ?? array_keys(config('site_homepage')) as $section)
@switch($section)
@case('hero')
<section class="hero hero-inspired"><div class="wrap hero-grid"><div class="hero-copy"><div class="hero-kicker"><span></span> @if(isset($homeCopy['hero.eyebrow']) && $homeCopy['hero.eyebrow'] !== config('site_homepage.hero.eyebrow')){{ $homeCopy['hero.eyebrow'] }}@else
HOME MOVERS AND CLEANERS
@endif</div><h1>{{ $siteSettings->get('hero_heading') }}</h1><p class="hero-tagline">{{ $siteSettings->get('tagline') }}</p><p class="hero-description">{{ $siteSettings->get('hero_description') }}</p><div class="hero-benefits" aria-label="Service highlights">
    <div><span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3 20 6v5c0 4.5-3 8-8 10-5-2-8-5.5-8-10V6l8-3Z"/><path d="m8.5 12 2.5 2.5 4.5-5"/></svg></span><strong>Reliable<br>service</strong></div>
    <div><span aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20v-2a6 6 0 0 1 12 0v2H3Zm12-5a5 5 0 0 1 6 5h-4"/></svg></span><strong>Experienced<br>team</strong></div>
    <div><span aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><strong>On-time<br>service</strong></div>
    <div><span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 2.1 5.8L20 11l-5.9 2.2L12 19l-2.1-5.8L4 11l5.9-2.2L12 3Z"/><path d="m19 16 .9 2.1L22 19l-2.1.9L19 22l-.9-2.1L16 19l2.1-.9L19 16Z"/></svg></span><strong>Clean, safe<br>&amp; hygienic</strong></div>
</div><div class="hero-actions"><a class="button" href="{{ route('booking') }}"><span aria-hidden="true">▦</span> Book a service <span aria-hidden="true">›</span></a><a class="button button-outline hero-explore-button" href="{{ route('services') }}"><span aria-hidden="true">▦</span> Explore our services</a></div></div><div class="hero-visual"><img src="{{ asset($homeImages['hero'] ?? 'images/moving-hero.webp') }}" width="1536" height="1024" alt="A mover carrying a box toward a new home, with a moving truck and teammate behind him" fetchpriority="high"><div class="hero-image-note"><span>✓</span><div><strong>@if(isset($homeCopy['hero.badge']) && $homeCopy['hero.badge'] !== config('site_homepage.hero.badge')){{ $homeCopy['hero.badge'] }}@else
Trusted moving &amp; cleaning partner
@endif</strong><small>Here to make your move easier</small></div></div></div></div></section>
@break
@case('services')
<section class="services-inspired"><div class="wrap"><div class="services-showcase"><div class="services-showcase-copy"><div class="services-showcase-kicker"><span></span> @if(isset($homeCopy['services.eyebrow']) && $homeCopy['services.eyebrow'] !== config('site_homepage.services.eyebrow')){{ $homeCopy['services.eyebrow'] }}@else
OUR SERVICES
@endif</div><h2>@if(isset($homeCopy['services.heading']) && $homeCopy['services.heading'] !== config('site_homepage.services.heading')){{ $homeCopy['services.heading'] }}@else
Complete Home Moving and Cleaning Solutions
@endif</h2><p>@if(isset($homeCopy['services.description']) && $homeCopy['services.description'] !== config('site_homepage.services.description')){{ $homeCopy['services.description'] }}@else
From moving your belongings safely to keeping your spaces spotless, we provide reliable, professional and affordable services for homes and businesses.
@endif</p><a class="button" href="{{ route('services') }}">View All Services <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h15m-6-6 6 6-6 6"/></svg></a><svg class="services-showcase-watermark" viewBox="0 0 160 160" aria-hidden="true"><path d="m18 74 62-54 62 54M34 64v77h92V64M65 141V94h30v47M57 76h16v16H57zm31 0h16v16H88z"/></svg></div><div class="services-showcase-grid">
@if($card = $services->firstWhere('slug', 'moving-services'))
<a class="service-showcase-card" href="{{ route('service', 'moving-services') }}"><div class="service-showcase-photo"><img src="{{ asset($card->photo() ?? 'images/service-moving.webp') }}" alt="Two movers carefully carrying a sofa" loading="lazy"><span class="service-showcase-icon" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M5 12h25v23H5zM30 20h8l6 7v8H30M12 40a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm26 0a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg></span></div><div class="service-showcase-content"><h3>{{ str_replace(' Services', '', $card->name) }}</h3><p>@if(isset($homeCopy['services.moving']) && $homeCopy['services.moving'] !== config('site_homepage.services.moving')){{ $homeCopy['services.moving'] }}@else
 From careful packing and furniture protection to transport, unpacking and room-by-room setup.
@endif</p><span class="service-showcase-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 12h15m-6-6 6 6-6 6"/></svg></span></div></a>
@endif
@if($card = $services->firstWhere('slug', 'fumigation-services'))
<a class="service-showcase-card" href="{{ route('service', 'fumigation-services') }}"><div class="service-showcase-photo"><img src="{{ asset($card->photo() ?? 'images/service-fumigation.png') }}" alt="Pest control specialist treating a garden" loading="lazy"><span class="service-showcase-icon" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M24 13v24M15 18l18 14M33 18 15 32M10 24h28M20 8l4 5 4-5M20 42l4-5 4 5M7 16l7 2-2-7M41 32l-7-2 2 7M41 16l-7 2 2-7M7 32l7-2-2 7"/></svg></span></div><div class="service-showcase-content"><h3>{{ str_replace(' Services', '', $card->name) }}</h3><p>@if(isset($homeCopy['services.fumigation']) && $homeCopy['services.fumigation'] !== config('site_homepage.services.fumigation')){{ $homeCopy['services.fumigation'] }}@else
Professional pest control for a healthier, safer environment.
@endif</p><span class="service-showcase-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 12h15m-6-6 6 6-6 6"/></svg></span></div></a>
@endif
@if($card = $services->firstWhere('slug', 'sofa-mattress-cleaning'))
<a class="service-showcase-card" href="{{ route('service', 'sofa-mattress-cleaning') }}"><div class="service-showcase-photo"><img src="{{ asset($card->photo() ?? 'images/service-sofa-cleaning.png') }}" alt="Upholstery cleaner deep cleaning a sofa" loading="lazy"><span class="service-showcase-icon" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M10 24v-7a5 5 0 0 1 5-5h18a5 5 0 0 1 5 5v7M7 24a4 4 0 0 1 4 4v9h26v-9a4 4 0 1 1 4 0v13H7V28a4 4 0 0 1 4-4M16 12v12m16-12v12"/></svg></span></div><div class="service-showcase-content"><h3>{{ str_replace(' Services', '', $card->name) }}</h3><p>@if(isset($homeCopy['services.upholstery']) && $homeCopy['services.upholstery'] !== config('site_homepage.services.upholstery')){{ $homeCopy['services.upholstery'] }}@else
Deep cleaning that restores freshness, comfort and hygiene.
@endif</p><span class="service-showcase-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 12h15m-6-6 6 6-6 6"/></svg></span></div></a>
@endif
@if($card = $services->firstWhere('slug', 'house-deep-cleaning'))
<a class="service-showcase-card" href="{{ route('service', 'house-deep-cleaning') }}"><div class="service-showcase-photo"><img src="{{ asset($card->photo() ?? 'images/service-house-cleaning.png') }}" alt="Cleaner mopping a bright living room floor" loading="lazy"><span class="service-showcase-icon" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="m5 22 19-16 19 16M10 19v23h28V19M19 42V28h10v14"/></svg></span></div><div class="service-showcase-content"><h3>{{ str_replace(' Services', '', $card->name) }}</h3><p>@if(isset($homeCopy['services.deep_cleaning']) && $homeCopy['services.deep_cleaning'] !== config('site_homepage.services.deep_cleaning')){{ $homeCopy['services.deep_cleaning'] }}@else
A thorough, top-to-bottom clean for a fresh and healthy home.
@endif</p><span class="service-showcase-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 12h15m-6-6 6 6-6 6"/></svg></span></div></a>
@endif
</div></div></div></section>
@php($realProjectPhotos = collect(config('site_content.project_photos', []))->only([0, 4, 5])->values())
<section class="real-work-section" aria-labelledby="real-work-heading">
    <div class="wrap">
        <div class="real-work-heading">
            <div>
                <div class="eyebrow">REAL WORK, REAL CARE</div>
                <h2 id="real-work-heading">See our team in action</h2>
                <p>From protecting furniture and loading the truck to cleaning and setting up a new home, our team helps with the details at every step.</p>
            </div>
            <a class="text-link" href="{{ route('gallery') }}">Browse project photos <span aria-hidden="true">→</span></a>
        </div>
        <div class="real-work-grid">
            @foreach($realProjectPhotos as $photo)
                <figure>
                    <img src="{{ asset($photo['path']) }}" alt="{{ $photo['alt_text'] }}" loading="lazy" decoding="async">
                    <figcaption>{{ $photo['caption'] }}</figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@break
@case('about')
<section class="section about-inspired"><div class="wrap story-grid"><div class="story-copy"><div class="about-kicker"><span aria-hidden="true"></span>@if(isset($homeCopy['about.eyebrow']) && $homeCopy['about.eyebrow'] !== config('site_homepage.about.eyebrow')){{ $homeCopy['about.eyebrow'] }}@else
A CLEAN, COMFORTABLE HOME
@endif</div><h2>@if(isset($homeCopy['about.heading']) && $homeCopy['about.heading'] !== config('site_homepage.about.heading')){{ $homeCopy['about.heading'] }}@else
Make Your Home Feel Like a Good Place to Land.
@endif</h2><p>@if(isset($homeCopy['about.description']) && $homeCopy['about.description'] !== config('site_homepage.about.description')){{ $homeCopy['about.description'] }}@else
Life moves fast. Our moving and cleaning services are here to make the process simpler, your spaces cleaner, and your home ready for what’s next.
@endif</p><div class="about-benefits" aria-label="What you can expect">
    <div>
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="m2.5 11.5 8-7 4 3.5M5 10v10h12v-7m-8 7v-5h4v5"/>
            <path d="M19 3.5c0 2.5 1.5 4 4 4-2.5 0-4 1.5-4 4 0-2.5-1.5-4-4-4 2.5 0 4-1.5 4-4Z"/>
        </svg>
        <strong>Clean &amp;<br>Hygienic</strong>
    </div>
    <div>
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 3 20 6v5c0 4.5-3 8-8 10-5-2-8-5.5-8-10V6l8-3Z"/>
            <path d="m8.5 12 2.5 2.5 4.5-5"/>
        </svg>
        <strong>Reliable<br>&amp; Trusted</strong>
    </div>
    <div>
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="7.5" r="3.5"/>
            <path d="M6.5 20v-2a5.5 5.5 0 0 1 11 0v2H6.5ZM5.5 5.5a3 3 0 0 0 0 6m13-6a3 3 0 0 1 0 6M2 19v-1a4.5 4.5 0 0 1 3-4.25M22 19v-1a4.5 4.5 0 0 0-3-4.25"/>
        </svg>
        <strong>Experienced<br>Team</strong>
    </div>
</div><div class="about-actions"><a class="button" href="{{ route('booking') }}">Book a service <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h15m-6-6 6 6-6 6"/></svg></a><a class="button button-outline" href="{{ in_array('steps', $homeSettings['order'] ?? array_keys(config('site_homepage')), true) ? route('home').'#how-it-works' : route('booking') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m10 8 6 4-6 4V8Z"/></svg>See how it works</a></div></div><div class="story-photo"><img src="{{ isset($homeImages['about']) ? asset($homeImages['about']) : 'images/fresh-start-living-room.webp' }}" alt="Sunlit living room with a comfortable sofa and house plants" loading="lazy"></div></div></section>
@break
@case('details')
<section class="details-inspired">
    <div class="wrap">
        <div class="details-heading">
            <div class="details-kicker"><span></span> @if(isset($homeCopy['details.eyebrow']) && $homeCopy['details.eyebrow'] !== config('site_homepage.details.eyebrow')){{ $homeCopy['details.eyebrow'] }}@else
HOW WE MAKE IT EASY
@endif <span></span></div>
            <h2>@if(isset($homeCopy['details.heading']) && $homeCopy['details.heading'] !== config('site_homepage.details.heading')){{ $homeCopy['details.heading'] }}@else
The details, made <span>simple.</span>
@endif</h2>
            <p>@if(isset($homeCopy['details.description']) && $homeCopy['details.description'] !== config('site_homepage.details.description')){{ $homeCopy['details.description'] }}@else
A straightforward process, transparent pricing, and a reliable team that gets it done right.
@endif</p>
        </div>
        <div class="details-grid">
            <article class="details-card">
                <div class="details-icon"><svg viewBox="0 0 96 96" aria-hidden="true"><rect x="19" y="22" width="58" height="57" rx="8"/><path d="M19 39h58M34 16v13M62 16v13M32 51h5m10 0h5m10 0h5M32 63h5m10 0h5"/><circle cx="70" cy="70" r="13" class="details-icon-fill"/><path d="m64 70 4 4 8-9" class="details-icon-check"/></svg></div>
                <h3>@if(isset($homeCopy['details.card_one_title']) && $homeCopy['details.card_one_title'] !== config('site_homepage.details.card_one_title')){{ $homeCopy['details.card_one_title'] }}@else
Simple Booking
@endif</h3>
                <p>@if(isset($homeCopy['details.card_one_text']) && $homeCopy['details.card_one_text'] !== config('site_homepage.details.card_one_text')){{ $homeCopy['details.card_one_text'] }}@else
Choose a service, pick a convenient date, and confirm. It’s that easy.
@endif</p>
            </article>
            <article class="details-card">
                <div class="details-icon"><svg viewBox="0 0 96 96" aria-hidden="true"><path d="m19 52 33-33h26v26L45 78a8 8 0 0 1-11 0L19 63a8 8 0 0 1 0-11Z"/><circle cx="66" cy="32" r="4"/><text x="28" y="59" transform="rotate(-45 43 52)">KSh</text></svg></div>
                <h3>@if(isset($homeCopy['details.card_two_title']) && $homeCopy['details.card_two_title'] !== config('site_homepage.details.card_two_title')){{ $homeCopy['details.card_two_title'] }}@else
Clear, Upfront Pricing
@endif</h3>
                <p>@if(isset($homeCopy['details.card_two_text']) && $homeCopy['details.card_two_text'] !== config('site_homepage.details.card_two_text')){{ $homeCopy['details.card_two_text'] }}@else
Know your cost before we start with no hidden charges.
@endif</p>
            </article>
            <article class="details-card">
                <div class="details-icon"><svg viewBox="0 0 96 96" aria-hidden="true"><circle cx="48" cy="25" r="10"/><circle cx="24" cy="34" r="8"/><circle cx="72" cy="34" r="8"/><path d="M30 58c1-11 8-18 18-18s17 7 18 18M10 59c1-9 6-15 14-15 5 0 9 2 12 7M86 59c-1-9-6-15-14-15-5 0-9 2-12 7"/><path d="M48 50 66 57v13c0 10-8 17-18 22-10-5-18-12-18-22V57l18-7Z" class="details-icon-shield"/><path d="m40 69 6 6 11-13" class="details-icon-shield-check"/></svg></div>
                <h3>@if(isset($homeCopy['details.card_three_title']) && $homeCopy['details.card_three_title'] !== config('site_homepage.details.card_three_title')){{ $homeCopy['details.card_three_title'] }}@else
Trusted &amp; Professional Team
@endif</h3>
                <p>@if(isset($homeCopy['details.card_three_text']) && $homeCopy['details.card_three_text'] !== config('site_homepage.details.card_three_text')){{ $homeCopy['details.card_three_text'] }}@else
Trained, courteous, and equipped to deliver a clean, safe, and hassle-free service.
@endif</p>
            </article>
        </div>
    </div>
</section>
@break
@case('steps')
<section id="how-it-works" class="steps-section steps-inspired">
    <div class="wrap">
        <div class="steps-heading">
            <div class="steps-kicker"><span></span> @if(isset($homeCopy['steps.eyebrow']) && $homeCopy['steps.eyebrow'] !== config('site_homepage.steps.eyebrow')){{ $homeCopy['steps.eyebrow'] }}@else
THREE EASY STEPS
@endif <span></span></div>
            <h2>@if(isset($homeCopy['steps.heading']) && $homeCopy['steps.heading'] !== config('site_homepage.steps.heading')){{ $homeCopy['steps.heading'] }}@else
From “I need a hand”<br>to “that feels better”.
@endif</h2>
            <p>@if(isset($homeCopy['steps.description']) && $homeCopy['steps.description'] !== config('site_homepage.steps.description')){{ $homeCopy['steps.description'] }}@else
Getting your moving or cleaning service is quick and hassle-free.
@endif</p>
        </div>
        <div class="steps-grid">
            <article class="steps-card">
                <div class="steps-card-visual steps-card-visual-booking" aria-hidden="true">
                    <svg viewBox="0 0 180 180"><rect x="49" y="13" width="82" height="151" rx="17" class="phone-frame"/><rect x="57" y="31" width="66" height="112" rx="5" class="phone-screen"/><path d="M76 22h28M70 62h40v42H70zM70 75h40M80 56v12m20-12v12M80 84h4m10 0h4m10 0h4M80 94h4m10 0h4" class="phone-calendar"/><circle cx="116" cy="109" r="17" class="phone-clock"/><path d="M116 99v11l7 5" class="phone-clock-hand"/></svg>
                </div>
                <span class="steps-number">1</span>
                <h3>@if(isset($homeCopy['steps.card_one_title']) && $homeCopy['steps.card_one_title'] !== config('site_homepage.steps.card_one_title')){{ $homeCopy['steps.card_one_title'] }}@else
Book Your Service
@endif</h3>
                <p>@if(isset($homeCopy['steps.card_one_text']) && $homeCopy['steps.card_one_text'] !== config('site_homepage.steps.card_one_text')){{ $homeCopy['steps.card_one_text'] }}@else
Choose the service you need and pick a convenient date.
@endif</p>
            </article>
            <span class="steps-connector" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M5 24h34M25 10l14 14-14 14"/></svg></span>
            <article class="steps-card">
                <div class="steps-card-visual"><img src="{{ asset($homeImages['steps_team'] ?? 'images/service-moving.webp') }}" alt="Our moving team carefully carrying a sofa" loading="lazy"></div>
                <span class="steps-number">2</span>
                <h3>@if(isset($homeCopy['steps.card_two_title']) && $homeCopy['steps.card_two_title'] !== config('site_homepage.steps.card_two_title')){{ $homeCopy['steps.card_two_title'] }}@else
We Get to Work
@endif</h3>
                <p>@if(isset($homeCopy['steps.card_two_text']) && $homeCopy['steps.card_two_text'] !== config('site_homepage.steps.card_two_text')){{ $homeCopy['steps.card_two_text'] }}@else
Our trained and vetted team arrives on time, ready to help.
@endif</p>
            </article>
            <span class="steps-connector" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M5 24h34M25 10l14 14-14 14"/></svg></span>
            <article class="steps-card">
                <div class="steps-card-visual"><img src="{{ isset($homeImages['steps_result']) ? asset($homeImages['steps_result']) : 'images/service-house-cleaning.webp' }}" alt="A clean, comfortable living room ready to enjoy" loading="lazy"></div>
                <span class="steps-number">3</span>
                <h3>@if(isset($homeCopy['steps.card_three_title']) && $homeCopy['steps.card_three_title'] !== config('site_homepage.steps.card_three_title')){{ $homeCopy['steps.card_three_title'] }}@else
Enjoy the Results
@endif</h3>
                <p>@if(isset($homeCopy['steps.card_three_text']) && $homeCopy['steps.card_three_text'] !== config('site_homepage.steps.card_three_text')){{ $homeCopy['steps.card_three_text'] }}@else
Relax in a clean, organized space. It’s that simple!
@endif</p>
            </article>
        </div>
        <div class="steps-action"><a class="button" href="{{ route('booking') }}">Book Now <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h15m-6-6 6 6-6 6"/></svg></a></div>
    </div>
</section>
@break
@case('testimonials')
@if($testimonials->isNotEmpty())
<section class="testimonials-section section">
    <div class="wrap">
        <div class="section-heading centered">
            <div class="eyebrow">{{ $homeCopy['testimonials.eyebrow'] ?? 'CUSTOMER EXPERIENCES' }}</div>
            <h2>{{ $homeCopy['testimonials.heading'] ?? 'Kind words from our customers.' }}</h2>
            <p>{{ $homeCopy['testimonials.description'] ?? 'Feedback shared by customers after their service.' }}</p>
        </div>
        <div class="testimonials-grid">
            @foreach($testimonials as $testimonial)
                <figure class="testimonial-card">
                    <blockquote>{{ $testimonial->quote }}</blockquote>
                    <figcaption><strong>{{ $testimonial->customer_name }}</strong>@if($testimonial->service)<span>{{ $testimonial->service }}</span>@endif</figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif
@break
@case('cta')
<section class="cta-section fresh-start-section">
    <img class="fresh-start-corner" src="{{ asset('images/fresh-start-orange-corner.svg') }}" alt="" aria-hidden="true">
    <div class="fresh-start-watermark" aria-hidden="true"></div>
    <div class="wrap fresh-start-inner">
        <div class="fresh-start-copy">
            <div class="fresh-start-kicker"><span></span> {{ $siteSettings['cta_eyebrow'] ?? 'A FRESH START CAN BE SIMPLE' }} <span></span></div>
            <h2>{{ $siteSettings['cta_heading'] ?? 'Ready to move or refresh your space?' }}</h2>
            <p>{{ $siteSettings['cta_description'] ?? 'Whether you’re moving, need a deep clean, or want a healthier, pest-free home, our team is here to help. We’ll make it happen quickly and professionally.' }}</p>
            <div class="fresh-start-actions">
                <a class="fresh-start-book" href="{{ route($siteSettings['cta_booking_destination'] ?? 'booking') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>{{ $siteSettings['cta_booking_label'] ?? 'Book a Service' }} <svg class="fresh-start-arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h15m-6-6 6 6-6 6"/></svg></a>
                <a class="fresh-start-whatsapp" href="{{ ($siteSettings['cta_whatsapp_destination'] ?? 'whatsapp') === 'contact' ? route('contact') : ($whatsappLink('Hello, I would like to request a quote for your services.') ?? route('contact')) }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 11.6a8.5 8.5 0 0 1-12.7 7.3L3 20l1.2-4.5a8.5 8.5 0 1 1 16.3-3.9Z"/><path d="M8.7 8.2c.2-.5.5-.5.8-.5h.5c.2 0 .4.1.5.4l.8 1.9c.1.2.1.4-.1.6l-.6.7c-.2.2-.2.4 0 .6.4.7 1.1 1.4 1.8 1.8.2.1.4.1.6-.1l.8-.9c.2-.2.4-.2.6-.1l1.8.9c.2.1.3.3.3.5 0 .4-.2 1.2-.7 1.6-.5.5-1.2.7-1.9.6-1-.2-2.3-.8-3.7-2.1-1.6-1.5-2.5-3.3-2.6-4.2-.1-.7.2-1.4.6-1.8Z"/></svg>{{ $siteSettings['cta_whatsapp_label'] ?? 'Chat on WhatsApp' }}</a>
            </div>
        </div>
        <div class="fresh-start-art" aria-hidden="true"><img src="{{ asset($siteSettings['cta_image'] ?? 'images/fresh-start-living-room.webp') }}" alt="" loading="lazy"></div>
    </div>
</section>
@break
@endswitch
@endforeach
@endsection
