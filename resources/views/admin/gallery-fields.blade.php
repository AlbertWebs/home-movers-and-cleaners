<label>{{ $item->exists ? 'Replace image' : 'Image' }}<input type="file" name="image" accept="image/jpeg,image/png,image/webp" @required(! $item->exists)></label>
<label>Category<select name="category" required>@foreach(\App\Models\GalleryItem::CATEGORIES as $category)<option @selected($item->category === $category)>{{ $category }}</option>@endforeach</select></label>
<label>Image description (alt text)<input name="alt_text" required maxlength="255" value="{{ $item->alt_text }}"></label>
<label>Caption<input name="caption" maxlength="160" value="{{ $item->caption }}"></label>
@if($item->exists)
    <label>Order<input type="number" name="sort_order" min="0" max="9999" required value="{{ $item->sort_order }}"></label>
    <label class="booking-inline-field"><input type="checkbox" name="is_published" value="1" @checked($item->is_published)> Published</label>
@endif
