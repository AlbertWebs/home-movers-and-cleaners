@extends('admin.layout')
@section('title', 'Settings')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">WEBSITE CONFIGURATION</div><h1>Business details</h1><p>Manage public contact information, homepage content, footer links and social channels.</p></div></div>
<form class="admin-panel settings-form" method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('partials.errors')

    <div class="settings-section">
        <h2>Company and contact</h2>
        <div class="form-row"><label>Company name<input name="company_name" required value="{{ old('company_name', $settings['company_name'] ?? '') }}"></label><label>Tagline<input name="tagline" value="{{ old('tagline', $settings['tagline'] ?? '') }}"></label></div>
        <div class="form-row"><label>Telephone<input name="phone" required value="{{ old('phone', $settings['phone'] ?? '') }}"></label><label>WhatsApp number<input name="whatsapp" required value="{{ old('whatsapp', $settings['whatsapp'] ?? '') }}"><small>Include country code, without + or spaces.</small></label></div>
        <div class="form-row"><label>Business email<input type="email" name="business_email" value="{{ old('business_email', $settings['business_email'] ?? '') }}"></label><label>Business location<input name="business_location" value="{{ old('business_location', $settings['business_location'] ?? '') }}"></label></div>
    </div>

    <div class="settings-section">
        <h2>Homepage</h2>
        <label>Hero heading<input name="hero_heading" required value="{{ old('hero_heading', $settings['hero_heading'] ?? '') }}"></label>
        <label>Hero description<textarea name="hero_description" rows="3" required>{{ old('hero_description', $settings['hero_description'] ?? '') }}</textarea></label>
        <label>About preview<textarea name="about_preview" rows="3" required>{{ old('about_preview', $settings['about_preview'] ?? '') }}</textarea></label>
    </div>

    <div class="settings-section">
        <h2>Fresh start call to action</h2>
        <label>Eyebrow<input name="cta_eyebrow" value="{{ old('cta_eyebrow', $settings['cta_eyebrow'] ?? 'A FRESH START CAN BE SIMPLE') }}"></label>
        <label>Heading<input name="cta_heading" value="{{ old('cta_heading', $settings['cta_heading'] ?? 'Ready to move or refresh your space?') }}"></label>
        <label>Description<textarea name="cta_description" rows="3">{{ old('cta_description', $settings['cta_description'] ?? 'Whether you’re moving, need a deep clean, or want a healthier, pest-free home, our team is here to help. We’ll make it happen quickly and professionally.') }}</textarea></label>
        <div class="form-row"><label>Booking button label<input name="cta_booking_label" value="{{ old('cta_booking_label', $settings['cta_booking_label'] ?? 'Book a Service') }}"></label><label>Booking button destination<select name="cta_booking_destination">@foreach(['booking' => 'Booking', 'services' => 'Services', 'contact' => 'Contact'] as $value => $label)<option value="{{ $value }}" @selected(old('cta_booking_destination', $settings['cta_booking_destination'] ?? 'booking') === $value)>{{ $label }}</option>@endforeach</select></label></div>
        <div class="form-row"><label>WhatsApp button label<input name="cta_whatsapp_label" value="{{ old('cta_whatsapp_label', $settings['cta_whatsapp_label'] ?? 'Chat on WhatsApp') }}"></label><label>WhatsApp button destination<select name="cta_whatsapp_destination">@foreach(['whatsapp' => 'WhatsApp', 'contact' => 'Contact page'] as $value => $label)<option value="{{ $value }}" @selected(old('cta_whatsapp_destination', $settings['cta_whatsapp_destination'] ?? 'whatsapp') === $value)>{{ $label }}</option>@endforeach</select></label></div>
        <label>CTA image<input type="file" name="cta_image_file" accept="image/jpeg,image/png,image/webp"><small>Leave empty to keep the current image.</small></label>
    </div>

    <div class="settings-section">
        <h2>Footer</h2>
        <label>Company description<textarea name="footer_description" rows="3">{{ old('footer_description', $settings['footer_description'] ?? 'Professional moving, fumigation, sofa and mattress cleaning, and house deep cleaning for homes and businesses.') }}</textarea></label>
        <label>Footer logo<input type="file" name="footer_logo_file" accept="image/jpeg,image/png,image/webp"><small>Leave empty to keep the current logo.</small></label>
        <input type="hidden" name="footer_house_watermark" value="0"><label><input type="checkbox" name="footer_house_watermark" value="1" @checked(old('footer_house_watermark', $settings['footer_house_watermark'] ?? '1') === '1')> Show decorative house watermark</label>
        <h3>Footer quick links</h3>
        @php($footerDefaults = [['Home', 'home'], ['About Us', 'about'], ['Services', 'services'], ['Moving Charges', 'pricing'], ['Book a Service', 'booking'], ['Contact Us', 'contact']])
        @foreach($footerDefaults as $index => [$defaultLabel, $defaultRoute])
            <div class="form-row"><label>Link {{ $index + 1 }} label<input name="footer_link_{{ $index + 1 }}_label" value="{{ old('footer_link_'.($index + 1).'_label', $settings['footer_link_'.($index + 1).'_label'] ?? $defaultLabel) }}"></label><label>Destination<select name="footer_link_{{ $index + 1 }}_route">@foreach(['home' => 'Home', 'about' => 'About Us', 'services' => 'Services', 'pricing' => 'Moving Charges', 'booking' => 'Book a Service', 'contact' => 'Contact Us'] as $value => $label)<option value="{{ $value }}" @selected(old('footer_link_'.($index + 1).'_route', $settings['footer_link_'.($index + 1).'_route'] ?? $defaultRoute) === $value)>{{ $label }}</option>@endforeach</select></label></div>
        @endforeach
    </div>

    <div class="settings-section">
        <h2>Social media</h2>
        <label>Facebook URL<input type="url" name="facebook" value="{{ old('facebook', $settings['facebook'] ?? '') }}"></label>
        <label>Instagram URL<input type="url" name="instagram" value="{{ old('instagram', $settings['instagram'] ?? '') }}"></label>
        <label>TikTok URL<input type="url" name="tiktok" value="{{ old('tiktok', $settings['tiktok'] ?? '') }}"></label>
        <label>YouTube URL<input type="url" name="youtube" value="{{ old('youtube', $settings['youtube'] ?? '') }}"></label>
    </div>
    <div class="settings-section">
        <h2>Brand and search</h2>
        <label>Operating hours<input name="operating_hours" value="{{ old('operating_hours', $settings['operating_hours'] ?? '') }}"></label>
        <label>Primary logo<input type="file" name="primary_logo_file" accept="image/jpeg,image/png,image/webp"></label>
        <label>Favicon<input type="file" name="favicon_file" accept="image/png"></label>
        <label>Default social image<input type="file" name="social_image_file" accept="image/jpeg,image/png,image/webp"></label>
        <label>Home search title<input name="seo_title" value="{{ old('seo_title', $settings['seo_title'] ?? '') }}"></label>
        <label>Home search description<textarea name="seo_description" rows="2">{{ old('seo_description', $settings['seo_description'] ?? '') }}</textarea></label>
        <label>Search engine visibility<select name="seo_robots">@foreach(['index, follow' => 'Allow indexing', 'noindex, follow' => 'Hide pages from search', 'noindex, nofollow' => 'Hide pages and stop link following'] as $value => $label)<option value="{{ $value }}" @selected(old('seo_robots', $settings['seo_robots'] ?? 'index, follow') === $value)>{{ $label }}</option>@endforeach</select></label>
        <details class="seo-editor-details"><summary>Search metadata for individual pages</summary>
            @foreach(['home' => 'Homepage', 'services' => 'Services', 'pricing' => 'Pricing', 'gallery' => 'Gallery', 'booking' => 'Booking', 'contact' => 'Contact', 'blog' => 'Resources'] as $seoRoute => $seoLabel)
                <fieldset class="seo-static-page-fields"><legend>{{ $seoLabel }}</legend>
                    @if($seoRoute !== 'home')
                        <label>Search title<input name="seo_{{ $seoRoute }}_title" value="{{ old('seo_'.$seoRoute.'_title', $settings['seo_'.$seoRoute.'_title'] ?? '') }}"></label>
                        <label>Search description<textarea name="seo_{{ $seoRoute }}_description" rows="2">{{ old('seo_'.$seoRoute.'_description', $settings['seo_'.$seoRoute.'_description'] ?? '') }}</textarea></label>
                    @endif
                    <label>Canonical URL<input type="url" name="seo_{{ $seoRoute }}_canonical_url" value="{{ old('seo_'.$seoRoute.'_canonical_url', $settings['seo_'.$seoRoute.'_canonical_url'] ?? '') }}" placeholder="Use this page address"></label>
                    <label>Search visibility<select name="seo_{{ $seoRoute }}_robots">@foreach(['' => 'Use site default', 'index, follow' => 'Allow indexing', 'noindex, follow' => 'Hide from search', 'noindex, nofollow' => 'Hide page and links'] as $value => $label)<option value="{{ $value }}" @selected(old('seo_'.$seoRoute.'_robots', $settings['seo_'.$seoRoute.'_robots'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label>Social title<input name="seo_{{ $seoRoute }}_og_title" value="{{ old('seo_'.$seoRoute.'_og_title', $settings['seo_'.$seoRoute.'_og_title'] ?? '') }}"></label>
                    <label>Social description<textarea name="seo_{{ $seoRoute }}_og_description" rows="2">{{ old('seo_'.$seoRoute.'_og_description', $settings['seo_'.$seoRoute.'_og_description'] ?? '') }}</textarea></label>
                    <label>Social image URL<input type="url" name="seo_{{ $seoRoute }}_og_image" value="{{ old('seo_'.$seoRoute.'_og_image', $settings['seo_'.$seoRoute.'_og_image'] ?? '') }}" placeholder="Use default social image"></label>
                </fieldset>
            @endforeach
        </details>
        <details class="seo-editor-details"><summary>Search and measurement setup</summary>
            <label>Default title suffix<input name="seo_title_suffix" value="{{ old('seo_title_suffix', $settings['seo_title_suffix'] ?? '| Home Movers and Cleaners') }}" placeholder="| Home Movers and Cleaners"></label>
            <label>Google Search Console verification token<input name="google_site_verification" value="{{ old('google_site_verification', $settings['google_site_verification'] ?? '') }}" autocomplete="off"></label>
            <label>Google Analytics 4 measurement ID<input name="ga4_measurement_id" value="{{ old('ga4_measurement_id', $settings['ga4_measurement_id'] ?? '') }}" placeholder="G-XXXXXXXXXX" autocomplete="off"></label>
            <label>Google Tag Manager container ID<input name="gtm_container_id" value="{{ old('gtm_container_id', $settings['gtm_container_id'] ?? '') }}" placeholder="GTM-XXXXXXX" autocomplete="off"></label>
            <p>Analytics loads only after a visitor chooses to allow analytics cookies. Verification and measurement IDs are public identifiers, not private credentials.</p>
        </details>
    </div>
    <button class="button">Save settings <span>↗</span></button>
</form>
@endsection
