@extends('layouts.app')
@section('title', $article->seo_title ?: $article->title.' | Home Movers and Cleaners')
@section('description', $article->seo_description ?: $article->excerpt)
@section('og-type', 'article')
@push('structured-data')<script type="application/ld+json">@json($articleSchema)</script>@endpush
@section('content')
<section class="page-hero"><div class="wrap"><nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><a href="{{ route('blog') }}">Resources</a><span aria-hidden="true">/</span><span aria-current="page">{{ $article->title }}</span></nav><div class="eyebrow">{{ $article->category }} · {{ $article->published_at->format('M j, Y') }}</div><h1>{{ $article->title }}</h1><p>{{ $article->excerpt }}</p></div></section>
<article class="section section-white"><div class="wrap blog-article-content">
    @if($article->featured_image)<img class="blog-article-image" src="{{ asset($article->featured_image) }}" alt="{{ $article->image_alt }}" fetchpriority="high">@endif
    <div class="site-prose">{!! \Illuminate\Support\Str::markdown($article->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
    <aside class="blog-article-cta"><h2>Need help with a move or a clean?</h2><p>Tell us what you need and our team can discuss the details with you.</p><a class="button" href="{{ route('booking') }}">Request a booking</a> <a class="text-link" href="{{ route('services') }}">Explore services <span aria-hidden="true">→</span></a></aside>
</div></article>
@endsection
