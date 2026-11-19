@extends('layouts.app')
@section('title', 'Moving and Cleaning Advice in Nairobi')
@section('description', 'Practical moving and home-cleaning guidance from Home Movers and Cleaners in Nairobi.')
@if($articles->isEmpty()) @section('robots', 'noindex, follow') @endif
@section('content')
<section class="page-hero"><div class="wrap"><div class="eyebrow">HELPFUL GUIDES</div><h1>Moving and cleaning advice</h1><p>Practical answers for planning a move and caring for your home.</p></div></section>
<section class="section section-white"><div class="wrap blog-grid">
    @forelse($articles as $article)
        <article class="blog-card">@if($article->featured_image)<a href="{{ route('blog.show', $article->slug) }}"><img src="{{ asset($article->featured_image) }}" alt="{{ $article->image_alt }}" loading="lazy"></a>@endif<div><span>{{ $article->category }} · {{ $article->published_at->format('M j, Y') }}</span><h2><a href="{{ route('blog.show', $article->slug) }}">{{ $article->title }}</a></h2><p>{{ $article->excerpt }}</p><a class="text-link" href="{{ route('blog.show', $article->slug) }}">Read article <span aria-hidden="true">→</span></a></div></article>
    @empty
        <div class="blog-empty"><h2>Helpful guides are on the way</h2><p>For service information now, explore our <a href="{{ route('services') }}">moving and cleaning services</a> or <a href="{{ route('contact') }}">ask our team</a>.</p></div>
    @endforelse
    {{ $articles->links() }}
</div></section>
@endsection
