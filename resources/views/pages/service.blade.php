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
<section class="page-hero">
    <div class="wrap catalog-hero">
        <div>
            <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><a href="{{ route('services') }}">Services</a><span aria-hidden="true">/</span><span aria-current="page">{{ $service->name }}</span></nav>
            <h1>{{ $service->name }}</h1>
            <p>{{ $service->summary }}</p>
            <a class="button" href="{{ route('booking', ['service' => $service->id]) }}">Request this service</a>
        </div>
        @if($service->photo())<div class="site-content-photo"><img src="{{ asset($service->photo()) }}" alt="{{ $service->name }}"></div>@endif
    </div>
</section>
<section class="section section-white">
    <div class="wrap detail-grid">
        <div class="site-prose">
            @if($service->description)<p>{{ $service->description }}</p>@endif
            {!! \Illuminate\Support\Str::markdown($service->content ?? $defaults['content'] ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
        </div>
        <aside class="detail-price">
            <h2>Service charges</h2>
            @forelse($service->packages->where('is_active', true) as $package)
                <a class="price-line" href="{{ route('booking', ['service' => $service->id, 'package' => $package->id]) }}"><span>{{ $package->name }}</span><strong>{{ $package->priceLabel() }}</strong><span aria-hidden="true">→</span></a>
            @empty
                <p>Request a Quote so we can review the work, property, and timing.</p>
            @endforelse
            <p class="small-note">We confirm your scope and the applicable price before the appointment.</p>
        </aside>
    </div>
</section>
@if($faqItems)
    <section class="section soft-section"><div class="wrap legal-copy site-faq">
        <h2>Frequently asked questions</h2>
        @foreach($faqItems as $faq)<details><summary>{{ $faq['question'] }}</summary><p>{{ $faq['answer'] }}</p></details>@endforeach
    </div></section>
@endif
<section class="cta-section"><div class="wrap cta-inner"><h2>Tell us what you need.</h2><a class="button button-light" href="{{ route('booking', ['service' => $service->id]) }}">Request a booking</a></div></section>
@endsection
