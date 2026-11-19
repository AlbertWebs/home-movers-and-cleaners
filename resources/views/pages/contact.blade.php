@extends('layouts.app')
@section('title', 'Contact Us | Home Movers and Cleaners')
@section('description', 'Contact Home Movers and Cleaners in Nairobi about moving, fumigation, upholstery cleaning, or house deep cleaning.')
@section('body-class', 'contact-page')
@section('content')
<section class="page-hero">
    <div class="wrap">
        <div class="eyebrow">WE’RE HERE TO HELP</div>
        <h1>Let’s talk about<br>what you need.</h1>
        <p>Send us a message or contact the team directly.</p>
    </div>
</section>
<section class="section section-white">
    <div class="wrap contact-grid">
        <div class="contact-details-panel">
            <div class="eyebrow">GET IN TOUCH</div>
            <h2>Choose what works for you.</h2>
            <p class="contact-intro-copy">Have a question, need a quote, or ready to book a service? We’re here to help.</p>
            <a class="contact-method" href="tel:{{ preg_replace('/\s+/', '', $siteSettings['phone'] ?? '0799948186') }}">
                <span class="contact-method-icon"><img src="{{ asset('images/contact-phone.svg') }}" alt=""></span>
                <span class="contact-method-copy"><small>Call us</small><strong>{{ $siteSettings['phone'] ?? '07999 48 186' }}</strong></span>
                <b aria-hidden="true">›</b>
            </a>
            @if($siteSettings['whatsapp'] ?? null)
                <a class="contact-method" href="https://wa.me/{{ $siteSettings['whatsapp'] }}" target="_blank" rel="noopener">
                    <span class="contact-method-icon contact-method-icon-whatsapp"><img src="{{ asset('images/whatsapp-symbol.svg') }}" alt=""></span>
                    <span class="contact-method-copy"><small>WhatsApp</small><strong>Start a conversation</strong></span>
                    <b aria-hidden="true">›</b>
                </a>
            @endif
            <div class="contact-method contact-method-static">
                <span class="contact-method-icon"><img src="{{ asset('images/contact-location.svg') }}" alt=""></span>
                <span class="contact-method-copy"><small>Our location</small><strong>{{ $siteSettings['business_location'] ?? 'Nairobi, Kenya' }}</strong></span>
            </div>
            @if($siteSettings['business_email'] ?? null)
                <a class="contact-method contact-method-email" href="mailto:{{ $siteSettings['business_email'] }}">
                    <span class="contact-method-icon"><img src="{{ asset('images/contact-email.svg') }}" alt=""></span>
                    <span class="contact-method-copy"><small>Email</small><strong>{{ $siteSettings['business_email'] }}</strong></span>
                    <b aria-hidden="true">›</b>
                </a>
            @endif
            @if($siteSettings['operating_hours'] ?? null)
                <div class="contact-hours"><span>OPERATING HOURS</span><strong>{{ $siteSettings['operating_hours'] }}</strong><small>East Africa Time (Nairobi)</small></div>
            @endif
            <a class="button contact-booking-link" href="{{ route('booking') }}"><span aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 10h18m-13 4h2m4 0h2m-8 4h2m4 0h2"/></svg></span>Request a booking <b aria-hidden="true">›</b></a>
            <div class="contact-illustration" aria-hidden="true"><img src="{{ asset('images/contact-moving-truck.webp') }}" alt="" loading="lazy"></div>
        </div>
        <form class="form-card contact-form-card" method="post" action="{{ route('contact.store') }}" data-ajax-form>
            @csrf
            <div class="contact-form-heading">
                <span class="contact-form-heading-icon" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M5 12h38v25H5z"/><path d="m6 14 18 14 18-14"/><path d="m6 36 13-11m23 11L29 25"/></svg></span>
                <span class="contact-form-heading-copy"><h2>Send an Enquiry</h2><p>Share a few details and our team will get back to you shortly.</p></span>
            </div>
            @include('partials.errors')
            <label for="contact-name"><span class="contact-label-text">Your name <span class="required-mark">*</span></span><span class="contact-control"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg><input id="contact-name" name="name" required value="{{ old('name') }}" autocomplete="name" placeholder="Enter your full name"></span></label>
            <div class="form-row">
                <label for="contact-phone"><span class="contact-label-text">Phone <span class="required-mark">*</span></span><span class="contact-control"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h3l2 5-2 2a14 14 0 0 0 4 4l2-2 5 2v3a2 2 0 0 1-2 2A16 16 0 0 1 5 5a2 2 0 0 1 2-2Z"/></svg><input id="contact-phone" name="phone" type="tel" required value="{{ old('phone') }}" autocomplete="tel" placeholder="e.g. 07999 48 186"></span></label>
                <label for="contact-email"><span class="contact-label-text">Email <span class="optional">Optional</span></span><span class="contact-control"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg><input id="contact-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="e.g. you@example.com"></span></label>
            </div>
            <label for="contact-subject"><span class="contact-label-text">Subject <span class="required-mark">*</span></span><span class="contact-control"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m20 13-7 7-10-10V3h7l10 10Z"/><circle cx="7.5" cy="7.5" r="1"/></svg><input id="contact-subject" name="subject" required value="{{ old('subject') }}" placeholder="e.g. Moving, Fumigation, Cleaning, etc."></span></label>
            <label for="contact-service"><span class="contact-label-text">Service of interest <span class="required-mark">*</span></span><span class="contact-control"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01"/></svg><select id="contact-service" name="service_id"><option value="">General enquiry</option>@foreach($services as $service)<option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>{{ $service->name }}</option>@endforeach</select></span></label>
            <label for="contact-message"><span class="contact-label-text">Your message <span class="required-mark">*</span></span><span class="contact-control contact-control-textarea"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-12.7 7.4L3 20l1.1-4.1A8.5 8.5 0 1 1 21 11.5Z"/><path d="M8 12h.01M12 12h.01M16 12h.01"/></svg><textarea id="contact-message" name="message" rows="4" required placeholder="Tell us more about your enquiry, such as location, preferred date, and number of rooms.">{{ old('message') }}</textarea></span></label>
            <div class="honeypot" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
            <div class="contact-form-footer">
                <button class="button" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7ZM22 2 11 13"/></svg>Send Enquiry <span aria-hidden="true">›</span></button>
                <p class="contact-response-note"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 8 3v5.5c0 5-3.3 8.3-8 10-4.7-1.7-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.2 2.2 4.8-4.8"/></svg>We typically respond within a few hours.</p>
            </div>
            <p class="contact-form-status" data-form-status role="status" tabindex="-1" aria-live="polite"></p>
        </form>
    </div>
</section>
@endsection
