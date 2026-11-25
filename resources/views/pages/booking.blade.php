@extends('layouts.app')
@section('title', 'Book Moving or Cleaning Services in Nairobi')
@section('description', 'Request moving, fumigation or cleaning services in Nairobi. Choose a service and preferred time, then send your request for review.')
@section('body-class', 'booking-page')
@section('content')
<section class="booking-hero">
    <div class="wrap booking-hero-inner">
        <div class="booking-hero-copy">
            <div class="booking-hero-kicker"><span></span>BOOK A SERVICE<span></span></div>
            <h1>Let’s get your service <span>booked.</span></h1>
            <p>Choose a service and a time that suits you. Our team will review your request and confirm the details.</p>
            <div class="booking-benefits" aria-label="Booking benefits">
                <div><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 10h18"/></svg><strong>Flexible scheduling</strong></div>
                <div><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 8 3v6c0 5.2-3.4 8.7-8 11-4.6-2.3-8-5.8-8-11V5l8-3Z"/><path d="m8.5 12 2.2 2.2 4.8-5"/></svg><strong>Trusted and professional</strong></div>
                <div><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m13 2-9 12h7l-1 8 10-13h-7l1-7Z"/></svg><strong>Quick confirmation</strong></div>
            </div>
        </div>
        <div class="booking-hero-visual" aria-hidden="true">
            <img src="{{ asset('images/moving-hero.webp') }}?v=057c068" alt="" fetchpriority="high">
            <svg class="booking-house-mark" viewBox="0 0 120 120"><path d="m12 53 48-42 48 42M25 48v60h70V48M48 108V75h24v33"/></svg>
        </div>
    </div>
</section>

<section class="booking-main-section">
    <div class="wrap booking-layout">
        <aside class="booking-aside">
            <div class="eyebrow">YOUR REQUEST</div>
            <h2>One step at a time.</h2>
            <ol class="booking-progress" data-booking-progress hidden>
                <li><span>Choose Service</span></li><li><span>Select Date and Time</span></li><li><span>Your Details</span></li><li><span>Review and Submit</span></li>
            </ol>
            <p class="booking-aside-note">Book moving, cleaning, or fumigation services in Nairobi. It only takes a few minutes.</p>
            <div class="booking-help-card">
                <span class="booking-help-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 13v-2a8 8 0 0 1 16 0v2M4 12H3a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h2v-7Zm16 0h1a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-2v-7ZM18 19a6 6 0 0 1-6 3h-1"/></svg></span>
                <span><strong>Need help booking?</strong><small>Our team is happy to help.</small></span>
                <a href="{{ $whatsappLink('Hello, I need help with a booking request.') ?? route('contact') }}" class="booking-help-link">Chat with us</a>
            </div>
        </aside>

        <form class="form-card booking-form" method="post" action="{{ route('booking.store') }}" enctype="multipart/form-data"
            data-booking-wizard data-availability-url="{{ route('booking.availability') }}"
            data-today="{{ now('Africa/Nairobi')->toDateString() }}">
            @csrf
            @include('partials.errors')
            <input type="hidden" name="request_token" value="{{ old('request_token', (string) \Illuminate\Support\Str::uuid()) }}">
            <div class="booking-form-heading">
                <h2 data-booking-heading>Choose a Service</h2>
                <span data-booking-step-label>Step 1 of 4</span>
            </div>
            <fieldset data-booking-step>
                <legend>Choose a service</legend>
                <div class="booking-services">
                    @foreach($services as $service)
                        <label class="booking-service-option">
                            <input type="radio" name="service_id" value="{{ $service->id }}" data-service-slug="{{ $service->slug }}" required @checked(old('service_id', request('service')) == $service->id)>
                            <span class="booking-service-icon" aria-hidden="true">
                                @if($service->slug === 'moving-services')<svg viewBox="0 0 48 48"><path d="M4 14h25v22H4zM29 22h8l7 8v6H29zM11 36a4 4 0 1 0 8 0m14 0a4 4 0 1 0 8 0"/><path d="M34 22v8h10"/></svg>
                                @elseif($service->slug === 'fumigation-services')<svg viewBox="0 0 48 48"><path d="M18 10h12v8H18zM21 18v6h-7v17h20V24h-7v-6M24 5v5M36 13l4-3M12 13l-4-3M36 22l5-1"/><path d="M19 31h10M24 26v10"/></svg>
                                @elseif($service->slug === 'sofa-mattress-cleaning')<svg viewBox="0 0 48 48"><path d="M9 25v-7a4 4 0 0 1 4-4h22a4 4 0 0 1 4 4v7M7 25h34v13H7zM11 38v4m26-4v4M7 29h34"/></svg>
                                @else<svg viewBox="0 0 48 48"><path d="m5 22 19-16 19 16M10 19v24h28V19M20 43V29h8v14M18 18h.1m12 0h.1"/><path d="m34 9 1.4 3.6L39 14l-3.6 1.4L34 19l-1.4-3.6L29 14l3.6-1.4L34 9Z"/></svg>@endif
                            </span>
                            <span><strong>{{ $service->name }}</strong><small>{{ $service->summary }}</small></span>
                        </label>
                    @endforeach
                </div>
                @if($services->isEmpty())
                    <p>Online booking is currently unavailable. <a href="{{ route('contact') }}">Contact us for help.</a></p>
                @endif
                <label>Package
                    <select name="price_package_id">
                        <option value="">Request a Quote</option>
                        @foreach($services as $service)
                            <optgroup label="{{ $service->name }}">
                                @foreach($service->packages as $package)
                                    <option value="{{ $package->id }}" data-package-service="{{ $service->id }}" data-pricing-type="{{ $package->pricing_type }}" @selected(old('price_package_id', request('package')) == $package->id)>{{ $package->name }} · {{ $package->priceLabel() }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </label>
            </fieldset>

            <fieldset data-booking-step>
                <legend>Choose a date and time</legend>
                <div class="booking-calendar" data-calendar hidden>
                    <div class="booking-calendar-header">
                        <button type="button" data-month-offset="-1" aria-label="Previous month">‹</button>
                        <strong data-month-title aria-live="polite"></strong>
                        <button type="button" data-month-offset="1" aria-label="Next month">›</button>
                    </div>
                    <div class="booking-calendar-grid" data-calendar-grid></div>
                    <p data-calendar-message role="status"></p>
                </div>
                <div class="booking-hours-panel" aria-label="Working hours">
                    <div class="booking-hours-heading">
                        <strong>Working hours</strong>
                        <span>Available start times in Nairobi time</span>
                    </div>
                    <ul class="booking-hours-list">
                        @foreach($workingHours as $label)
                            <li>{{ $label }}</li>
                        @endforeach
                    </ul>
                    <p>Choose a date to see which times are available.</p>
                </div>
                <label data-date-field>Preferred date
                    <input type="date" name="preferred_date" min="{{ now('Africa/Nairobi')->toDateString() }}" value="{{ old('preferred_date') }}" required>
                </label>
                <label>Preferred time
                    <input type="time" name="preferred_time" value="{{ old('preferred_time') }}" required>
                </label>
            </fieldset>

            <fieldset data-booking-step>
                <legend>Your details</legend>
                <label><span class="booking-label-text">Full name <span class="required-mark">Required</span></span><input name="customer_name" required value="{{ old('customer_name') }}" autocomplete="name" placeholder="Enter your full name"></label>
                <div class="form-row">
                    <label><span class="booking-label-text">Phone <span class="required-mark">Required</span></span><input type="tel" name="phone" required value="{{ old('phone') }}" autocomplete="tel" placeholder="e.g. 07999 48 186"></label>
                    <label><span class="booking-label-text">Email <span class="optional">Optional</span></span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com"></label>
                </div>
                <label data-location>Service location<input name="location" value="{{ old('location') }}" autocomplete="street-address" placeholder="Estate, street, building, or area"></label>
                <div class="form-row" data-moving-addresses>
                    <label>Pickup address<input name="pickup_location" value="{{ old('pickup_location') }}"></label>
                    <label>Destination address<input name="destination" value="{{ old('destination') }}"></label>
                </div>
                <label><span data-instructions-label>Additional instructions</span><textarea name="instructions" rows="4" placeholder="Share property size, rooms, items, access notes, or cleaning needs"></textarea></label>
                <label data-quote-photos hidden>Photos for your quote <span class="optional">Optional</span>
                    <input type="file" name="quote_photos[]" accept="image/jpeg,image/png,image/webp" multiple data-max-files="5" data-max-size="5120">
                    <small>Upload up to 5 JPG, PNG, or WebP photos, up to 5 MB each. Photos are shared privately with our booking team.</small>
                </label>
            </fieldset>

            <fieldset data-booking-step hidden>
                <legend>Review your request</legend>
                <dl class="booking-review" data-review></dl>
                <p>Our team will confirm availability, scope, and pricing before your appointment.</p>
            </fieldset>
            <p data-form-status role="status" tabindex="-1"></p>
            <div class="booking-controls">
                <button type="button" class="button button-outline" data-booking-back hidden>Back</button>
                <button type="button" class="button" data-booking-next hidden>Continue</button>
                @if($services->isNotEmpty())
                    <button class="button" type="submit" data-booking-submit>Send booking request</button>
                @endif
            </div>
        </form>
    </div>
</section>
@endsection
