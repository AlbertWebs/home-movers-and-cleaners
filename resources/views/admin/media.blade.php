@extends('admin.layout')
@section('title', 'Media Library')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">WEBSITE ASSETS</div><h1>Media library</h1><p>Upload, search, replace, and keep track of images used across the site.</p></div></div>
<section class="admin-panel media-upload-panel">
    <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" data-upload-progress-form>
        @csrf
        <div class="form-row">
            <label>Add images<input type="file" name="files[]" accept="image/jpeg,image/png,image/webp" multiple required><small>JPG, PNG, or WebP. Up to 20 images, 10 MB each. Images are resized and saved as WebP when the server supports it.</small></label>
            <label>Category<select name="category" required>@foreach(['moving' => 'Moving', 'fumigation' => 'Fumigation', 'upholstery' => 'Sofa and mattress', 'home' => 'Home cleaning', 'brand' => 'Brand', 'other' => 'Other'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
        </div>
        <progress data-upload-progress max="100" value="0" hidden></progress>
        <p data-upload-status role="status" aria-live="polite"></p>
        <button class="button" type="submit">Upload to library <span aria-hidden="true">↗</span></button>
    </form>
</section>
<section class="admin-panel">
    <form method="get" action="{{ route('admin.media') }}" class="booking-admin-filters">
        <label>Search images<input name="search" value="{{ request('search') }}" placeholder="Title, file name, or alt text"></label>
        <label>Category<select name="category"><option value="">All categories</option>@foreach(['moving' => 'Moving', 'fumigation' => 'Fumigation', 'upholstery' => 'Sofa and mattress', 'home' => 'Home cleaning', 'brand' => 'Brand', 'other' => 'Other'] as $value => $label)<option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>@endforeach</select></label>
        <button class="button" type="submit">Find images</button>
    </form>
    @if($assets->isEmpty())
        <div class="admin-empty"><h3>No matching images.</h3><p>Upload an image above or adjust the search filters.</p></div>
    @else
        <div class="media-library-grid">
            @foreach($assets as $asset)
                <article class="media-library-card">
                    <a href="{{ $asset->url() }}" target="_blank" rel="noopener"><img src="{{ $asset->url() }}" alt="{{ $asset->alt_text }}" loading="lazy"></a>
                    <form method="post" action="{{ route('admin.media.update', $asset) }}" enctype="multipart/form-data" class="media-asset-form">
                        @csrf @method('PATCH')
                        <label>Title<input name="title" value="{{ $asset->title }}" maxlength="255" required></label>
                        <label>Alt text<input name="alt_text" value="{{ $asset->alt_text }}" maxlength="255" required></label>
                        <label>Category<select name="category">@foreach(['moving' => 'Moving', 'fumigation' => 'Fumigation', 'upholstery' => 'Sofa and mattress', 'home' => 'Home cleaning', 'brand' => 'Brand', 'other' => 'Other'] as $value => $label)<option value="{{ $value }}" @selected($asset->category === $value)>{{ $label }}</option>@endforeach</select></label>
                        <label>Replace image<input type="file" name="replacement" accept="image/jpeg,image/png,image/webp"><small>Replacing updates website references.</small></label>
                        <div class="media-asset-meta"><span>{{ number_format($asset->size / 1024) }} KB</span><span>{{ $asset->category }}</span><span>{{ $asset->usage_count }} uses</span></div>
                        <div class="media-asset-actions"><button class="mini-button" type="submit">Save details</button><button class="mini-button" type="button" data-copy-value="storage/{{ $asset->path }}">Copy path</button></div>
                    </form>
                    @if($asset->usage_count === 0)
                        <form method="post" action="{{ route('admin.media.delete', $asset) }}" class="media-delete-form">@csrf @method('DELETE')<button class="text-link" type="submit">Remove unused image</button></form>
                    @else
                        <p class="small-note">This image is in use and cannot be removed.</p>
                    @endif
                    <p class="media-original-name">{{ $asset->original_name }}</p>
                </article>
            @endforeach
        </div>
        <div class="pagination">{{ $assets->links() }}</div>
    @endif
</section>
@endsection
