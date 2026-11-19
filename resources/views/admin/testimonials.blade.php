@extends('admin.layout')
@section('title', 'Testimonials')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">CUSTOMER FEEDBACK</div><h1>Testimonials</h1><p>Add customer feedback only when you have permission to publish it.</p></div></div>
<section class="admin-panel">
    <div class="panel-head"><div><h2>Add feedback</h2><p>Unpublished entries stay out of the public website.</p></div></div>
    <form class="settings-form" method="post" action="{{ route('admin.testimonials.store') }}">
        @csrf
        <div class="form-row"><label>Customer name<input name="customer_name" maxlength="120" required value="{{ old('customer_name') }}"></label><label>Service, optional<input name="service" maxlength="120" value="{{ old('service') }}"></label></div>
        <label>Customer feedback<textarea name="quote" rows="3" maxlength="1200" required>{{ old('quote') }}</textarea></label>
        <div class="form-row"><label>Display order<input type="number" name="sort_order" min="0" max="9999" value="{{ old('sort_order', 0) }}" required></label><label class="booking-inline-field"><input type="checkbox" name="is_published" value="1"> Publish on homepage</label></div>
        <label class="booking-inline-field"><input type="checkbox" name="consent_confirmed" value="1" required> I have permission from this customer to publish their feedback.</label>
        <button class="button" type="submit">Save feedback</button>
    </form>
</section>
<section class="admin-panel">
    <div class="panel-head"><div><h2>Saved feedback</h2><p>Review the text and publication state.</p></div></div>
    @forelse($testimonials as $testimonial)
        <div class="testimonial-admin-card">
        <form class="settings-form testimonial-update-form" method="post" action="{{ route('admin.testimonials.update', $testimonial) }}">
            @csrf @method('PATCH')
            <div class="form-row"><label>Customer name<input name="customer_name" maxlength="120" required value="{{ $testimonial->customer_name }}"></label><label>Service, optional<input name="service" maxlength="120" value="{{ $testimonial->service }}"></label></div>
            @if($testimonial->consent_confirmed_at)<p class="consent-stamp">Permission confirmed {{ $testimonial->consent_confirmed_at->format('j M Y, H:i') }}.</p>@endif
            <label>Customer feedback<textarea name="quote" rows="3" maxlength="1200" required>{{ $testimonial->quote }}</textarea></label>
            <div class="form-row"><label>Display order<input type="number" name="sort_order" min="0" max="9999" value="{{ $testimonial->sort_order }}" required></label><label class="booking-inline-field"><input type="checkbox" name="is_published" value="1" @checked($testimonial->is_published)> Publish on homepage</label></div>
            <label class="booking-inline-field"><input type="checkbox" name="consent_confirmed" value="1" required> I have permission from this customer to publish their feedback.</label>
            <button class="mini-button" type="submit">Save changes</button>
        </form>
        <form method="post" action="{{ route('admin.testimonials.delete', $testimonial) }}" onsubmit="return confirm('Remove this customer feedback?')">@csrf @method('DELETE')<button class="mini-button" type="submit">Remove</button></form>
        </div>
    @empty
        <div class="admin-empty"><p>No customer feedback has been added.</p></div>
    @endforelse
    {{ $testimonials->links() }}
</section>
@endsection
