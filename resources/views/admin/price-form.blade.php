<form method="post" class="settings-form" action="{{ $item->exists ? route('admin.pricing.update', $item) : route('admin.pricing.store', $service) }}">
    @csrf
    @if($item->exists) @method('PATCH') @endif
    <label>Package name<input name="name" required value="{{ $item->name }}"></label>
    <label>Included in the package<textarea name="description" rows="2">{{ $item->description }}</textarea></label>
    <div class="form-row">
        <label>Price type<select name="pricing_type">@foreach(['starting' => 'Starting price', 'fixed' => 'Fixed price', 'quote' => 'Quote only'] as $value => $label)<option value="{{ $value }}" @selected($item->pricing_type === $value)>{{ $label }}</option>@endforeach</select></label>
        <label>Amount (KSh)<input type="number" min="0" name="amount" value="{{ $item->amount }}"></label>
        <label>Order<input type="number" name="sort_order" min="0" max="9999" required value="{{ $item->sort_order ?? 0 }}"></label>
    </div>
    <label class="booking-inline-field"><input type="checkbox" name="is_active" value="1" @checked($item->is_active)> Shown on site</label>
    <label class="booking-inline-field"><input type="checkbox" name="is_featured" value="1" @checked($item->is_featured)> Featured package</label>
    <button class="button" type="submit">Save package</button>
</form>
