@extends('layouts.app')
@section('title', $service->seo_title ?: $service->name.' in Nairobi | Home Movers and Cleaners')
@section('description', $service->seo_description ?: $service->summary)
@php
    $defaults = config('site_content.services.'.$service->slug, []);
    $faqItems = $service->faqs ?? $defaults['faqs'] ?? [];
    $serviceSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            ['@type' => 'Service', 'name' => $service->name, 'description' => $service->summary, 'url' => route('service', $service->slug), 'provider' => ['@type' => 'Organization', 'name' => $siteSettings['company_name'] ?? 'Home Movers and Cleaners', 'url' => route('home')], 'areaServed' => $siteSettings['business_location'] ?? 'Nairobi, Kenya'],
            ['@type' => 'BreadcrumbList', 'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => route('services')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $service->name, 'item' => route('service', $service->slug)],
            ]],
        ],
    ];
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($faqItems)->map(function (array $faq): array {
            return ['@type' => 'Question', 'name' => $faq['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']]];
        })->values(),
    ];
@endphp
@push('structured-data')
    <script type="application/ld+json">@json($serviceSchema)</script>
    @if($faqItems)
        <script type="application/ld+json">@json($faqSchema)</script>
    @endif
@endpush
@section('content')
<section class="page-hero service-detail-hero">
    <div class="wrap catalog-hero">
        <div>
            <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><a href="{{ route('services') }}">Services</a><span aria-hidden="true">/</span><span aria-current="page">{{ $service->name }}</span></nav>
            <h1>{{ $service->name }}</h1>
            <p>{{ $service->summary }}</p>
            <a class="button" href="{{ route('booking', ['service' => $service->id]) }}">Request this service</a>
        </div>
        @if($service->photo())<div class="site-content-photo"><img src="{{ asset($service->photo()) }}" alt="{{ $service->name }} service in Nairobi" fetchpriority="high"></div>@endif
    </div>
</section>
@if($service->slug === 'moving-services')
    <section class="moving-hours" aria-labelledby="moving-hours-title">
        <div class="wrap moving-hours-inner">
            <div class="moving-hours-heading">
                <span class="eyebrow">PLAN YOUR MOVE</span>
                <h2 id="moving-hours-title">Moving hours</h2>
            </div>
            <ul class="moving-hours-list">
                @foreach(\App\BookingSchedule::HOURS as $label)
                    <li>{{ $label }}</li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
<section class="section section-white service-detail-content">
    <div class="wrap detail-grid">
        <div class="site-prose">
            @if($service->description)<p>{{ $service->description }}</p>@endif
            {!! \Illuminate\Support\Str::markdown($service->content ?? $defaults['content'] ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
        </div>
        <aside class="detail-price">
            <h2>Service charges</h2>
            <p class="detail-price-intro">Choose a package to start your booking. Listed prices are starting rates.</p>
            @forelse($service->packages->where('is_active', true) as $package)
                <a class="price-line" href="{{ route('booking', ['service' => $service->id, 'package' => $package->id]) }}"><span>{{ $package->name }}</span><strong>{{ $package->priceLabel() }}</strong><span aria-hidden="true">→</span></a>
            @empty
                <p>Request a Quote so we can review the work, property, and timing.</p>
            @endforelse
            <p class="small-note">We confirm the details and final price with you before the appointment.</p>
            <a class="detail-quote-link" href="{{ route('booking', ['service' => $service->id]) }}">Need a custom quote? <span aria-hidden="true">→</span></a>
        </aside>
    </div>
    @if($service->slug === 'moving-services')
        @php($movingScope = $defaults['scope'] ?? [])
        <div class="wrap moving-scope">
            <div class="moving-scope-heading">
                <div class="eyebrow">WHAT YOUR MOVE INCLUDES</div>
                <h2>A complete move, from packing to settling in</h2>
                <p>Our end-to-end service is designed to make house and office relocations smoother. We handle your belongings with care and help you get comfortable in your new space.</p>
            </div>
            <div class="moving-scope-grid">
                @foreach($movingScope as $scopeGroup)
                    <article class="moving-scope-card">
                        <h3>{{ $scopeGroup['title'] }}</h3>
                        <ul>
                            @foreach($scopeGroup['items'] as $item)
                                <li><span aria-hidden="true">✓</span>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
            <p class="moving-scope-note">Tell us about your property, access and priorities when you request a quote. We’ll confirm the agreed work and price before the move.</p>
            <div class="moving-project-photos" aria-label="Our moving and settling-in work">
                @foreach(config('site_content.project_photos', []) as $photo)
                    <figure>
                        <img src="{{ asset($photo['path']) }}" alt="{{ $photo['alt_text'] }}" loading="lazy" decoding="async">
                        <figcaption>{{ $photo['caption'] }}</figcaption>
                    </figure>
                @endforeach
            </div>
            <a class="moving-scope-gallery-link" href="{{ route('gallery') }}">See more of our work <span aria-hidden="true">→</span></a>
        </div>
    @endif
</section>
@if($faqItems)
    <section class="section soft-section"><div class="wrap legal-copy site-faq">
        <h2>Frequently asked questions</h2>
        @foreach($faqItems as $faq)<details><summary>{{ $faq['question'] }}</summary><p>{{ $faq['answer'] }}</p></details>@endforeach
    </div></section>
@endif
<section class="cta-section"><div class="wrap cta-inner"><h2>Tell us what you need.</h2><a class="button button-light" href="{{ route('booking', ['service' => $service->id]) }}">Request a booking</a></div></section>
@endsection
