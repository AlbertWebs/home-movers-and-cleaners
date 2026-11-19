@extends('admin.layout')
@section('title', 'Pages')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">WEBSITE CONTENT</div><h1>Pages and policies</h1><p>Manage website pages, customer policies, and the terms customers agree to.</p></div></div>
@include('partials.errors')
<div class="admin-content-grid pages-workspace">
    <section class="admin-panel">
        <h2>Website pages</h2>
        <a class="button" href="{{ route('admin.pages') }}">New page</a>
        <a class="admin-terms-shortcut" href="{{ route('admin.pages', ['edit' => 'terms']) }}">
            <span><strong>Terms of Service</strong><small>Update the terms shown to customers.</small></span>
            <span class="text-link">Edit terms <b aria-hidden="true">→</b></span>
        </a>
        <ul class="admin-page-list">
            @foreach($pages as $page)
                <li><a href="{{ route('admin.pages', ['edit' => $page->slug]) }}">{{ $page->title }}</a><small>{{ $page->is_published ? 'Published' : 'Draft' }}</small></li>
            @endforeach
        </ul>
    </section>
    <form class="admin-panel settings-form" method="post" action="{{ route('admin.pages.save') }}" enctype="multipart/form-data" data-seo-editor data-seo-default-title="{{ $selected->title }}" data-seo-default-description="{{ $selected->summary }}" data-seo-default-url="{{ $selected->slug === 'about' ? route('about') : (in_array($selected->slug, ['privacy', 'terms'], true) ? route($selected->slug) : route('content.show', $selected->slug ?: 'new-page')) }}">
        @csrf
        <input type="hidden" name="page_id" value="{{ old('page_id', $selected->id) }}">
        <h2>{{ $selected->slug ? 'Edit '.$selected->title : 'Create a page' }}</h2>
        <label>Title<input name="title" required value="{{ old('title', $selected->title) }}"></label>
        <label>Page address<input name="slug" required pattern="[a-z0-9]+(-[a-z0-9]+)*" value="{{ old('slug', $selected->slug) }}"></label>
        <label>Intro<textarea name="summary" rows="2" required>{{ old('summary', $selected->summary) }}</textarea></label>
        @include('admin.markdown-field', ['fieldName' => 'body', 'fieldLabel' => 'Page content', 'fieldValue' => old('body', $selected->body), 'required' => true])
        <label>Featured image<input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"></label>
        <label>Image alt text<input name="image_alt" value="{{ old('image_alt', $selected->image_alt) }}"></label>
        <label>Search title<input name="seo_title" value="{{ old('seo_title', $selected->seo_title) }}"></label>
        <label>Search description<textarea name="seo_description" rows="2">{{ old('seo_description', $selected->seo_description) }}</textarea></label>
        <div class="seo-snippet-preview"><small>Search result preview</small><strong data-seo-preview-title></strong><p data-seo-preview-description></p><span data-seo-preview-url></span></div>
        <details class="seo-editor-details"><summary>Canonical and social sharing</summary>
            <label>Canonical URL<input type="url" name="canonical_url" value="{{ old('canonical_url', $selected->canonical_url) }}" placeholder="Leave blank to use this page address"></label>
            <label>Search visibility<select name="seo_robots">@foreach(['' => 'Use site default', 'index, follow' => 'Allow indexing', 'noindex, follow' => 'Hide from search, follow links', 'noindex, nofollow' => 'Hide from search and stop link following'] as $value => $label)<option value="{{ $value }}" @selected(old('seo_robots', $selected->seo_robots ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label>Social title<input name="og_title" value="{{ old('og_title', $selected->og_title) }}"></label>
            <label>Social description<textarea name="og_description" rows="2">{{ old('og_description', $selected->og_description) }}</textarea></label>
            <label>Social image URL<input type="url" name="og_image" value="{{ old('og_image', $selected->og_image) }}" placeholder="Use the page image when blank"></label>
        </details>
        <label>Visibility<select name="is_published"><option value="0">Draft</option><option value="1" @selected(old('is_published', $selected->is_published))>Published</option></select></label>
        <p>Publish legal pages once the business has approved the wording.</p>
        <button class="button" type="submit">Save page</button>
        @if($selected->exists)
            <a class="text-link" href="{{ route('admin.pages.preview', $selected) }}">Preview saved page</a>
        @endif
    </form>
</div>
@endsection
