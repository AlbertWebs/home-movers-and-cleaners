@extends('layouts.app')
@section('title', 'Booking Status | Home Movers and Cleaners')
@section('robots', 'noindex, nofollow')
@section('content')
<section class="section section-white">
    <div class="wrap confirmation-card">
        <div class="confirm-intro">
            <div class="confirm-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg></div>
            <div class="eyebrow">BOOKING STATUS</div>
            <h1>{{ $booking->status === 'Pending' ? 'Request received' : 'Your booking status' }}</h1>
            <p>{{ $booking->status === 'Pending' ? 'Thanks for choosing Home Movers and Cleaners. Our team will contact you to confirm availability and discuss the details.' : ($booking->status === 'Expired' ? 'This quotation is no longer valid. Contact us if you would like an updated quotation.' : 'Here are the latest details for your booking.') }}</p>
        </div>
        <div class="confirm-reference">
            <div><span>BOOKING REFERENCE</span><strong>{{ $booking->reference }}</strong></div>
            <small>Keep this reference for your records</small>
        </div>
        <dl class="confirm-summary">
            <div><dt>Status</dt><dd><span class="confirm-status">{{ $booking->status }}</span></dd></div>
            <div><dt>Service</dt><dd>{{ $booking->service->name }}</dd></div>
            <div><dt>Package</dt><dd>{{ $booking->package_name ?: 'Request a quote' }}</dd></div>
            @if($booking->listed_price)<div><dt>Estimated price</dt><dd>KSh {{ number_format($booking->listed_price) }}</dd></div>@endif
            @if($booking->preferred_date)<div><dt>Preferred time</dt><dd>{{ $booking->preferred_date->format('j M Y') }} at {{ $booking->preferred_time }} <span>(Nairobi time)</span></dd></div>@endif
        </dl>
        @if($booking->status === 'Quoted')<p class="booking-confirmation-note">This quotation is valid until {{ $booking->quoteExpiresAt()?->timezone('Africa/Nairobi')->format('j M Y g:i A') }} Nairobi time.</p>@endif
        @if($booking->status === 'Expired' && $booking->quoteExpiresAt())<p class="booking-confirmation-note">This quotation expired on {{ $booking->quoteExpiresAt()->timezone('Africa/Nairobi')->format('j M Y g:i A') }} Nairobi time. Contact us if you would like an updated quotation.</p>@endif
        <div class="confirm-actions">
            <a class="button" href="{{ route('contact') }}">Contact us about this booking <span aria-hidden="true">›</span></a>
            <a class="button confirm-home-link" href="{{ route('home') }}">Back to home</a>
        </div>
    </div>
</section>
@endsection
