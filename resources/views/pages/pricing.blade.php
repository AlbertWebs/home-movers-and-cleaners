@extends('layouts.app')
@section('title', 'Moving and Cleaning Prices in Nairobi | Home Movers and Cleaners')
@section('description', 'Compare current moving, fumigation, upholstery cleaning and deep-cleaning prices in Kenyan shillings. Request a quote for other needs.')
@section('body-class', 'pricing-page')
@section('content')
<section class="page-hero pricing-hero">
    <div class="wrap pricing-hero-inner">
        <div class="pricing-hero-copy">
            <div class="eyebrow"><span></span>SERVICE CHARGES</div>
            <h1>Clear prices for a smoother start.</h1>
            <p>Compare service packages in Kenyan shillings. Choose a listed option or ask us for a quote that fits your needs.</p>
            <div class="pricing-highlights">
                <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/><circle cx="12" cy="12" r="10"/></svg>Upfront package rates</span>
                <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>Prices in KSh</span>
                <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h16m-7-7 7 7-7 7"/></svg>Easy online booking</span>
            </div>
        </div>
        <a class="pricing-hero-action" href="{{ route('booking') }}">
            <span class="pricing-action-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 10h18m-13 4h2m4 0h2m-8 4h2m4 0h2"/></svg></span>
            <span><strong>Ready to book?</strong><small>Pick a service and preferred time.</small></span>
            <span aria-hidden="true">→</span>
        </a>
    </div>
</section>
<section class="pricing-section">
    <div class="wrap">
        <div class="pricing-section-heading">
            <div><div class="eyebrow">CHOOSE WHAT WORKS FOR YOU</div><h2>Service packages</h2></div>
            <p>Select a package to begin a booking request. We will confirm the scope and final details with you.</p>
        </div>
        <div class="price-tables">
        @foreach($services as $service)
            <article class="price-table">
                <div class="price-table-head">
                    <span class="price-service-icon" aria-hidden="true">
                        @if($service->slug === 'moving-services')
                            <svg viewBox="0 0 48 48"><path d="M4 14h25v22H4zM29 22h8l7 8v6H29zM11 36a4 4 0 1 0 8 0m14 0a4 4 0 1 0 8 0"/><path d="M34 22v8h10"/></svg>
                        @elseif($service->slug === 'fumigation-services')
                            <svg viewBox="0 0 48 48"><path d="M18 10h12v8H18zM21 18v6h-7v17h20V24h-7v-6M24 5v5M36 13l4-3M12 13 8 10M36 22l5-1"/><path d="M19 31h10M24 26v10"/></svg>
                        @elseif($service->slug === 'sofa-mattress-cleaning')
                            <svg viewBox="0 0 48 48"><path d="M9 25v-7a4 4 0 0 1 4-4h22a4 4 0 0 1 4 4v7M7 25h34v13H7zM11 38v4m26-4v4M7 29h34"/></svg>
                        @else
                            <svg viewBox="0 0 48 48"><path d="m5 22 19-16 19 16M10 19v24h28V19M20 43V29h8v14"/><path d="m34 9 1.4 3.6L39 14l-3.6 1.4L34 19l-1.4-3.6L29 14l3.6-1.4L34 9Z"/></svg>
                        @endif
                    </span>
                    <span class="price-service-heading"><h2>{{ $service->name }}</h2><small>{{ $service->packages->count() }} packages</small></span>
                    <a class="price-service-details" href="{{ route('service', $service->slug) }}">Service details <span aria-hidden="true">↗</span></a>
                </div>
                @forelse($service->packages as $package)
                    <a class="price-line" href="{{ route('booking', ['service' => $service->id, 'package' => $package->id]) }}">
                        <span class="price-package-copy"><span class="price-package-name">{{ $package->name }} @if($package->is_featured)<small class="price-featured">Popular</small>@endif</span>
                            @if($package->description)<small class="price-description">{{ $package->description }}</small>@endif
                        </span>
                        <strong class="price-amount">{{ $package->priceLabel() }}</strong><span class="price-line-arrow" aria-hidden="true">→</span>
                    </a>
                @empty
                    <a class="text-link" href="{{ route('booking', ['service' => $service->id]) }}">Request a Quote</a>
                @endforelse
            </article>
        @endforeach
        </div>
    </div>
</section>
<section class="section soft-section"><div class="wrap legal-copy site-faq">
    <h2>Questions about pricing</h2>
    <details><summary>What does a starting price mean?</summary><p>It is the starting amount for the named package. Property size, access and additional work can affect the final quote.</p></details>
    <details><summary>What if I can’t find my needs on this list?</summary><p>Choose Request a Quote and describe the work you need. The team will check availability and discuss pricing with you.</p></details>
</div></section>
<section class="cta-section"><div class="wrap cta-inner"><h2>Ready to request a service?</h2><a class="button button-light" href="{{ route('booking') }}">Book a service</a></div></section>
@endsection
