@extends('admin.layout')
@section('title', 'Resource centre')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">CONTENT AND SEARCH</div><h1>Resource centre</h1><p>Write useful answers to customer questions. Drafts stay private until you publish them.</p></div></div>
<div class="admin-content-grid">
    <section class="admin-panel"><h2>Articles</h2><a class="button" href="{{ route('admin.blog.index') }}">New article</a><ul class="admin-page-list">@foreach($articles as $article)<li><a href="{{ route('admin.blog.index', ['edit' => $article->id]) }}">{{ $article->title }}</a><small>{{ ucfirst($article->status) }}{{ $article->published_at ? ' · '.$article->published_at->format('M j, Y') : '' }}</small></li>@endforeach</ul>{{ $articles->links() }}</section>
    <form class="admin-panel settings-form" method="post" action="{{ $selected->exists ? route('admin.blog.update', $selected) : route('admin.blog.store') }}" enctype="multipart/form-data" data-seo-editor data-seo-default-title="{{ $selected->title ?: 'Article title' }} | Home Movers and Cleaners" data-seo-default-description="{{ $selected->excerpt }}" data-seo-default-url="{{ $selected->exists ? route('blog.show', $selected->slug) : route('blog') }}">
        @csrf @if($selected->exists) @method('PATCH') @endif
        <h2>{{ $selected->exists ? 'Edit article' : 'Create article' }}</h2>
        <label>Title<input name="title" required maxlength="180" value="{{ old('title', $selected->title) }}"></label>
        <label>URL slug<input name="slug" required pattern="[a-z0-9]+(-[a-z0-9]+)*" value="{{ old('slug', $selected->slug) }}" placeholder="how-to-prepare-for-moving-day"></label>
        <label>Category<input name="category" required maxlength="80" value="{{ old('category', $selected->category) }}"></label>
        <label>Summary<textarea name="excerpt" rows="3" required maxlength="500">{{ old('excerpt', $selected->excerpt) }}</textarea></label>
        @include('admin.markdown-field', ['editorId' => 'article-'.$selected->id, 'fieldName' => 'body', 'fieldLabel' => 'Article content', 'fieldValue' => old('body', $selected->body), 'required' => true])
        <label>Featured image<input type="file" name="featured_image_file" accept="image/jpeg,image/png,image/webp"></label>
        <label>Image alt text<input name="image_alt" value="{{ old('image_alt', $selected->image_alt) }}"></label>
        <label>SEO title<input name="seo_title" value="{{ old('seo_title', $selected->seo_title) }}"></label>
        <label>SEO description<textarea name="seo_description" rows="2">{{ old('seo_description', $selected->seo_description) }}</textarea></label>
        <div class="seo-snippet-preview"><small>Search result preview</small><strong data-seo-preview-title></strong><p data-seo-preview-description></p><span data-seo-preview-url></span></div>
        <details class="seo-editor-details"><summary>Canonical and social sharing</summary>
            <label>Canonical URL<input type="url" name="canonical_url" value="{{ old('canonical_url', $selected->canonical_url) }}"></label>
            <label>Search visibility<select name="seo_robots">@foreach(['' => 'Use site default', 'index, follow' => 'Allow indexing', 'noindex, follow' => 'Hide from search', 'noindex, nofollow' => 'Hide page and links'] as $value => $label)<option value="{{ $value }}" @selected(old('seo_robots', $selected->seo_robots ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label>Social title<input name="og_title" value="{{ old('og_title', $selected->og_title) }}"></label>
            <label>Social description<textarea name="og_description" rows="2">{{ old('og_description', $selected->og_description) }}</textarea></label>
            <label>Social image URL<input type="url" name="og_image" value="{{ old('og_image', $selected->og_image) }}"></label>
        </details>
        <label>Status<select name="status"><option value="draft" @selected(old('status', $selected->status ?? 'draft') === 'draft')>Draft</option><option value="published" @selected(old('status', $selected->status ?? '') === 'published')>Published</option></select></label>
        <label>Publish date<input type="datetime-local" name="published_at" value="{{ old('published_at', $selected->published_at?->format('Y-m-d\TH:i')) }}"><small>Leave blank to publish now when status is Published.</small></label>
        <button class="button" type="submit">Save article</button>
        @if($selected->exists && $selected->status === 'published')<a class="text-link" href="{{ route('blog.show', $selected->slug) }}" target="_blank" rel="noopener">View published article <span aria-hidden="true">↗</span></a>@endif
    </form>
</div>
@endsection
