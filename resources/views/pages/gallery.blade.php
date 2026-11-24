@extends('layouts.app')
@section('title', 'Moving and Cleaning Project Gallery | Nairobi')
@section('description', 'Browse genuine project photos from Home Movers and Cleaners when published by the team.')
@section('content')
@php
    $featuredPhotos = $featuredPhotos ?? [];
    $galleryCategories = collect($featuredPhotos)->pluck('category')->merge($galleryItems->pluck('category'))->unique()->sort();
@endphp
<section class="page-hero"><div class="wrap"><div class="eyebrow">OUR PROJECTS</div><h1>Home, in good hands.</h1><p>A look at the real moving and cleaning work shared by our team.</p></div></section>
<section class="section section-white">
    <div class="wrap" data-project-gallery>
        @if($galleryItems->isEmpty() && empty($featuredPhotos))
            <div class="empty-gallery"><h2>Looking for examples of our work?</h2><p>Ask us about the service you need, or browse the services we offer.</p><a class="button" href="{{ route('services') }}">Explore services</a></div>
        @else
            <div class="gallery-filters" role="group" aria-label="Filter photos" hidden>
                <button type="button" data-gallery-filter="" aria-pressed="true">All photos</button>
                @foreach($galleryCategories as $category)
                    <button type="button" data-gallery-filter="{{ $category }}" aria-pressed="false">{{ $category }}</button>
                @endforeach
            </div>
            <div class="gallery-grid">
                @foreach($featuredPhotos as $photo)
                    <figure data-gallery-category="{{ $photo['category'] }}">
                        <a href="{{ asset($photo['path']) }}" data-gallery-zoom>
                            <img src="{{ asset($photo['path']) }}" alt="{{ $photo['alt_text'] }}" loading="lazy" decoding="async">
                        </a>
                        @if($photo['caption'])<figcaption>{{ $photo['caption'] }}</figcaption>@endif
                    </figure>
                @endforeach
                @foreach($galleryItems as $item)
                    <figure data-gallery-category="{{ $item->category }}">
                        <a href="{{ Storage::disk('public')->url($item->path) }}" data-gallery-zoom>
                            <img src="{{ Storage::disk('public')->url($item->path) }}" alt="{{ $item->alt_text }}" loading="lazy" decoding="async">
                        </a>
                        @if($item->caption)<figcaption>{{ $item->caption }}</figcaption>@endif
                    </figure>
                @endforeach
            </div>
            <dialog class="gallery-dialog" aria-label="Project photo">
                <form method="dialog"><button type="submit" class="button">Close photo</button></form>
                <img alt=""><p></p>
            </dialog>
        @endif
    </div>
</section>
@endsection
