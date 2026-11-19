@php($defaults = config('site_content.services.'.$item->slug, []))
<form class="settings-form" method="post" action="{{ $item->exists ? route('admin.services.update', $item) : route('admin.services.store') }}" enctype="multipart/form-data" data-seo-editor data-seo-default-title="{{ $item->name ?: 'Service name' }} in Nairobi | Home Movers and Cleaners" data-seo-default-description="{{ $item->summary }}" data-seo-default-url="{{ $item->exists ? route('service', $item->slug) : route('services') }}">
    @csrf
    @if($item->exists) @method('PATCH') @endif
    <label>Service name<input name="name" required value="{{ $item->name }}"></label>
    @unless($item->exists)<label>URL name<input name="slug" required pattern="[a-z0-9]+(-[a-z0-9]+)*"></label>@endunless
    <label>Short description<textarea name="summary" required rows="2">{{ $item->summary }}</textarea></label>
    <label>Introduction<textarea name="description" rows="3">{{ $item->description }}</textarea></label>
    @include('admin.markdown-field', ['editorId' => $item->id ?? 'new', 'fieldName' => 'content', 'fieldLabel' => 'Service details', 'fieldValue' => $item->content ?? $defaults['content'] ?? '', 'required' => false])
    <label>Benefits, one per line<textarea name="benefits_text" rows="3">{{ implode(PHP_EOL, $item->benefits ?? $defaults['benefits'] ?? []) }}</textarea></label>
    <label>FAQs, one per line. Separate the question and answer with |<textarea name="faqs_text" rows="4">{{ collect($item->faqs ?? $defaults['faqs'] ?? [])->map(fn ($faq) => $faq['question'].' | '.$faq['answer'])->implode(PHP_EOL) }}</textarea></label>
    <label>Service photo<input type="file" name="service_photo" accept="image/jpeg,image/png,image/webp"></label>
    <div class="form-row">
        <label>Search title<input name="seo_title" value="{{ $item->seo_title }}"></label>
        <label>Order<input type="number" name="sort_order" min="0" max="9999" value="{{ $item->sort_order ?? 0 }}"></label>
    </div>
    <label>Search description<textarea name="seo_description" rows="2">{{ $item->seo_description }}</textarea></label>
    <div class="seo-snippet-preview"><small>Search result preview</small><strong data-seo-preview-title></strong><p data-seo-preview-description></p><span data-seo-preview-url></span></div>
    <details class="seo-editor-details"><summary>Canonical and social sharing</summary>
        <label>Canonical URL<input type="url" name="canonical_url" value="{{ $item->canonical_url }}" placeholder="Leave blank to use this page address"></label>
        <label>Search visibility<select name="seo_robots">@foreach(['' => 'Use site default', 'index, follow' => 'Allow indexing', 'noindex, follow' => 'Hide from search, follow links', 'noindex, nofollow' => 'Hide from search and stop link following'] as $value => $label)<option value="{{ $value }}" @selected(($item->seo_robots ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label>Social title<input name="og_title" value="{{ $item->og_title }}"></label>
        <label>Social description<textarea name="og_description" rows="2">{{ $item->og_description }}</textarea></label>
        <label>Social image URL<input type="url" name="og_image" value="{{ $item->og_image }}" placeholder="Use the service photo when blank"></label>
    </details>
    <label class="booking-inline-field"><input type="checkbox" name="is_active" value="1" @checked($item->is_active)> Published</label>
    <button class="button" type="submit">Save service</button>
</form>
