@extends('admin.layout')
@section('title', 'Gallery')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">MEDIA</div><h1>Project gallery</h1><p>Use approved project photos, and file before-and-after photos under their own category only when genuine.</p></div></div>
@include('partials.errors')
<form class="admin-panel settings-form" method="post" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data">
    @csrf
    <h2>Add a gallery photo</h2>
    @include('admin.gallery-fields', ['item' => new \App\Models\GalleryItem])
    <button class="button">Upload photo</button>
</form>
<div class="admin-gallery-grid">
    @forelse($items as $item)
        <article class="admin-gallery-card">
            <img src="{{ Storage::disk('public')->url($item->path) }}" alt="{{ $item->alt_text }}" loading="lazy">
            <div>
                <details><summary>{{ $item->caption ?: $item->category }}</summary>
                    <form class="settings-form" method="post" action="{{ route('admin.gallery.update', $item) }}" enctype="multipart/form-data">
                        @csrf @method('PATCH')
                        @include('admin.gallery-fields')
                        <button class="mini-button">Save photo</button>
                    </form>
                </details>
                <form method="post" action="{{ route('admin.gallery.delete', $item) }}" onsubmit="return confirm('Remove this gallery image?')">
                    @csrf @method('DELETE')
                    <button class="delete-link">Remove image</button>
                </form>
            </div>
        </article>
    @empty
        <div class="admin-empty"><h3>No photos added yet.</h3><p>Upload approved project photos to have them appear on the public gallery page.</p></div>
    @endforelse
</div>
<div class="pagination">{{ $items->links() }}</div>
@endsection
