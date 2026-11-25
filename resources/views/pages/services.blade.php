@extends('layouts.app')
@section('title', 'Moving, Fumigation and Cleaning Services in Nairobi')
@section('description', 'Compare household moving, pest-control, sofa and mattress cleaning, and house deep cleaning services in Nairobi.')
@section('content')
@php
    $serviceImages = [
        'moving-services' => 'images/about-moving.webp',
        'fumigation-services' => 'images/about-fumigation.webp',
        'sofa-mattress-cleaning' => 'images/about-upholstery.webp',
        'house-deep-cleaning' => 'images/about-deep-cleaning.webp',
    ];
    $serviceHighlights = [
        'moving-services' => ['Residential and office moves', 'Loading and transport', 'Packing and careful handling', 'Planning for bulky items'],
        'fumigation-services' => ['Pest identification and treatment', 'Preparation guidance for your property', 'Safe and effective products', 'Follow-up advice'],
        'sofa-mattress-cleaning' => ['Deep upholstery cleaning', 'Mattress sanitization', 'Stain and odour removal', 'Care suited to your fabric'],
        'house-deep-cleaning' => ['Complete room-by-room cleaning', 'Floor and surface care', 'Kitchen and bathroom deep clean', 'Move-in and move-out cleaning'],
    ];
@endphp
<main class="services-page">
    <section class="services-intro">
        <div class="wrap services-intro-inner">
            <div class="services-intro-copy">
                <div class="eyebrow">OUR SERVICES</div>
                <h1>Services Designed Around Your Home</h1>
                <p>Professional moving, fumigation, upholstery and deep cleaning services for homes and businesses in Nairobi.</p>
                <span class="services-intro-underline" aria-hidden="true"></span>
            </div>
            <div class="services-intro-art" aria-hidden="true"></div>
        </div>
    </section>

    <section class="services-catalog-section" aria-label="Home services">
        <div class="wrap catalog-grid">
            @forelse($services as $service)
                @php
                    $servicePhoto = $serviceImages[$service->slug] ?? $service->photo();
                    $serviceBenefits = $service->benefits ?: ($serviceHighlights[$service->slug] ?? config('site_content.services.'.$service->slug.'.benefits', []));
                    $serviceIcon = config('site_content.services.'.$service->slug.'.icon');
                @endphp
                <article class="catalog-card">
                    @if($servicePhoto)
                        <img class="catalog-photo" src="{{ asset($servicePhoto) }}?v=057c068" alt="{{ $service->name }}" loading="lazy">
                    @endif
                    <div class="catalog-card-body">
                        <div class="services-card-heading">
                            @if($serviceIcon)
                                <span class="services-card-icon"><img src="{{ asset($serviceIcon) }}" alt="" loading="lazy"></span>
                            @endif
                            <div>
                                <h2>{{ $service->name }}</h2>
                                <p>{{ $service->slug === 'moving-services' ? 'End-to-end house and office moves, from careful packing and protected transport to unpacking and home setup.' : $service->summary }}</p>
                            </div>
                        </div>
                        @if($serviceBenefits)
                            <ul class="services-benefits">
                                @foreach($serviceBenefits as $benefit)
                                    <li><svg viewBox="0 0 20 20" aria-hidden="true"><path d="m5 10 3.1 3.1L15 6.5"/></svg>{{ $benefit }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="services-card-actions">
                            <a class="button" href="{{ route('booking', ['service' => $service->id]) }}">Book a Service <span aria-hidden="true">→</span></a>
                            <a class="services-explore-link" href="{{ route('service', $service->slug) }}">Explore Service <span aria-hidden="true">↗</span></a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="services-empty-state"><h2>Talk to us about your home.</h2><p>Contact the team to check available services and discuss your needs.</p><a class="button" href="{{ route('contact') }}">Contact us</a></div>
            @endforelse
        </div>
    </section>

    <section class="cta-section">
        <div class="wrap cta-inner"><h2>See service charges before you book.</h2><a class="button button-light" href="{{ route('pricing') }}">View pricing</a></div>
    </section>
</main>
@endsection
