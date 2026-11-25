@extends('layouts.app')
@section('title', $page->seo_title ?: $page->title)
@section('description', $page->seo_description ?: $page->summary)
@section('body-class', $page->slug === 'about' ? 'about-page' : '')
@if($preview)
    @section('robots', 'noindex, nofollow')
@elseif($legalDraft ?? false)
    @section('robots', 'noindex, follow')
@endif
@section('content')
@if($page->slug === 'about')
    @php($aboutServiceImages = [
        'moving-services' => 'images/about-moving.webp',
        'fumigation-services' => 'images/about-fumigation.webp',
        'sofa-mattress-cleaning' => 'images/about-upholstery.webp',
        'house-deep-cleaning' => 'images/about-deep-cleaning.webp',
    ])
    <section class="about-intro about-hero" aria-labelledby="about-hero-title">
        <div class="wrap about-intro-grid">
            <div class="about-intro-copy">
                <div class="about-intro-kicker">ABOUT HOME MOVERS AND CLEANERS <span></span></div>
                <h1 id="about-hero-title">Moving and cleaning,<br>made easier.</h1>
                <p class="about-intro-summary">{{ $page->summary }}</p>
                <div class="about-details site-prose">
                    {!! \Illuminate\Support\Str::markdown($page->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                </div>
                <div class="about-intro-actions">
                    <a class="about-primary-action" href="{{ route('contact') }}">Contact Our Team <span aria-hidden="true">→</span></a>
                    <a class="about-whatsapp-action" href="{{ $whatsappLink('Hello, I would like to learn more about Home Movers and Cleaners.') ?? route('contact') }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset('images/whatsapp-symbol.svg') }}" alt="">
                        Chat on WhatsApp
                    </a>
                </div>
            </div>
            <div class="about-team-photo">
                <img src="{{ asset($aboutServiceImages['moving-services']) }}?v=057c068" alt="Professional movers carrying a wrapped sofa beside their truck" fetchpriority="high">
                <div class="about-hero-badge">
                    <span class="about-hero-badge-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5 20 6v5.4c0 5-3.3 8.4-8 10.1-4.7-1.7-8-5.1-8-10.1V6l8-3.5Z"/><path d="m8.5 12 2.2 2.2 4.8-4.8"/></svg></span>
                    <span><strong>Trusted moving and cleaning team</strong><small>Here to make your move easier</small></span>
                </div>
            </div>
        </div>
    </section>
    <section class="about-services">
        <div class="wrap">
            <div class="about-services-heading">
                <span></span><h2>Our Services at a Glance</h2><span></span>
                <p>Explore our core services and choose what you need.</p>
            </div>
            <div class="about-service-cards">
                @forelse($services as $service)
                    <article class="about-service-card">
                        <div class="about-service-photo" aria-hidden="true">
                            @php($aboutServicePhoto = $aboutServiceImages[$service->slug] ?? $service->photo())
                            @if($aboutServicePhoto)
                                <img src="{{ asset($aboutServicePhoto) }}?v=057c068" alt="" loading="lazy">
                            @endif
                        </div>
                        <div class="about-service-card-copy">
                            <h3>{{ $service->name }}</h3>
                            <p>{{ $service->summary }}</p>
                            <a href="{{ route('service', $service->slug) }}">Learn More <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                @empty
                    <p class="about-services-empty">Contact our team to find the right service for your home.</p>
                @endforelse
            </div>
        </div>
    </section>
    <section class="about-why-section">
        <div class="wrap about-values-strip">
            <div class="about-values-heading"><h2>Why<br>Choose Us?</h2><span></span></div>
            <article class="about-value-item">
                <svg viewBox="0 0 48 48" aria-hidden="true"><path d="m24 4 16 6v12c0 11-7 18-16 22C15 40 8 33 8 22V10l16-6Z"/><path d="m17 24 5 5 10-11"/></svg>
                <span><strong>Reliable Service</strong><small>A team you can count on.</small></span>
            </article>
            <article class="about-value-item">
                <svg viewBox="0 0 48 48" aria-hidden="true"><rect x="7" y="11" width="34" height="31" rx="3"/><path d="M15 6v10m18-10v10M7 20h34M16 27h4m8 0h4m-16 8h4m8 0h4"/></svg>
                <span><strong>Flexible Scheduling</strong><small>Book a time that works for you.</small></span>
            </article>
            <article class="about-value-item">
                <svg viewBox="0 0 48 48" aria-hidden="true"><circle cx="24" cy="15" r="8"/><circle cx="10" cy="19" r="5"/><circle cx="38" cy="19" r="5"/><path d="M9 42v-5c0-7 6-12 15-12s15 5 15 12v5M2 39v-3c0-5 3-8 8-9m36 12v-3c0-5-3-8-8-9"/></svg>
                <span><strong>Experienced Team</strong><small>Skilled, careful and professional.</small></span>
            </article>
            <article class="about-value-item">
                <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M42 6C20 6 8 14 7 31c0 6 4 10 10 10C34 40 42 26 42 6Z"/><path d="M5 44c9-13 17-20 28-28"/></svg>
                <span><strong>Complete Care</strong><small>Moving, cleaning and fumigation in one place.</small></span>
            </article>
        </div>
    </section>
@else
<section class="page-hero">
    <div class="wrap">
        <div class="eyebrow">{{ $preview ? 'ADMIN PREVIEW' : 'HOME MOVERS AND CLEANERS' }}</div>
        <h1>{{ $page->title }}</h1>
        <p>{{ $page->summary }}</p>
    </div>
</section>
@if($legalDraft ?? false)
    <div class="wrap legal-draft-notice" role="status"><strong>Draft for review</strong><span>This policy is not final. Please confirm its terms with the business before relying on it.</span></div>
@endif
<section class="section section-white">
    <div class="wrap {{ $page->image ? 'detail-grid' : 'legal-copy' }}">
        <div class="site-prose">
            {!! \Illuminate\Support\Str::markdown($page->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
            <a class="text-link" href="{{ route('contact') }}">Contact our team <span aria-hidden="true">→</span></a>
        </div>
        @if($page->image)
            <div class="site-content-photo"><img src="{{ asset($page->image) }}" alt="{{ $page->image_alt }}" loading="lazy"></div>
        @endif
    </div>
</section>
@endif
@endsection
