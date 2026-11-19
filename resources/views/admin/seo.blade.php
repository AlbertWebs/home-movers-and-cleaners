@extends('admin.layout')
@section('title', 'SEO and redirects')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">SEARCH VISIBILITY</div><h1>SEO overview</h1><p>Live checks from published CMS entries. Search engine indexing and performance data require Search Console access.</p></div><a class="button button-outline" href="{{ route('sitemap') }}" target="_blank" rel="noopener">Open XML sitemap <span aria-hidden="true">↗</span></a></div>
<div class="seo-overview-grid">
    <article><span>Indexable CMS entries</span><strong>{{ $indexedCount }}</strong><small>Published services, pages, and articles</small></article>
    <article><span>Missing SEO titles</span><strong>{{ $missingTitles }}</strong><small>Among indexable CMS entries</small></article>
    <article><span>Missing descriptions</span><strong>{{ $missingDescriptions }}</strong><small>Among indexable CMS entries</small></article>
    <article><span>Published articles</span><strong>{{ $publishedArticles }}</strong><small>Visible in the resource centre</small></article>
</div>
<div class="seo-admin-grid">
    <section class="admin-panel">
        <h2>Create redirect</h2>
        <p>Use a local path such as <code>/old-page</code> or a complete HTTP/HTTPS destination. Redirect chains and self redirects should be avoided.</p>
        <form class="settings-form" method="post" action="{{ route('admin.seo.redirects.store') }}">
            @csrf
            <label>Old path<input name="from_path" required placeholder="/old-page"></label>
            <label>Destination<input name="destination" required placeholder="/services/moving-services"></label>
            <label>Redirect type<select name="status_code"><option value="301">301 permanent</option><option value="302">302 temporary</option></select></label>
            <button class="button" type="submit">Add redirect</button>
        </form>
    </section>
    <section class="admin-panel">
        <h2>Redirect manager</h2>
        @forelse($redirects as $redirect)
            <form class="seo-redirect-row" method="post" action="{{ route('admin.seo.redirects.update', $redirect) }}">
                @csrf @method('PATCH')
                <label>From<input name="from_path" required value="{{ $redirect->from_path }}"></label>
                <label>To<input name="destination" required value="{{ $redirect->destination }}"></label>
                <label>Code<select name="status_code"><option value="301" @selected($redirect->status_code === 301)>301</option><option value="302" @selected($redirect->status_code === 302)>302</option></select></label>
                <label class="booking-inline-field"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($redirect->is_active)> Active</label>
                <button class="button button-small" type="submit">Save</button>
                @if($redirect->history)<details><summary>History ({{ count($redirect->history) }})</summary><ul>@foreach($redirect->history as $entry)<li>{{ $entry['changed_at'] ?? '' }}: {{ $entry['status_code'] ?? '' }} to {{ $entry['destination'] ?? '' }}</li>@endforeach</ul></details>@endif
            </form>
        @empty
            <p>No redirects are configured yet.</p>
        @endforelse
        {{ $redirects->links() }}
    </section>
</div>
<section class="admin-panel seo-audit-note"><h2>What this overview measures</h2><p>It counts published, indexable CMS services, pages, and articles and flags missing CMS SEO titles and descriptions. It does not claim to crawl every rendered heading, internal link, image alt attribute, schema validity, or search ranking. Review the sitemap and validate structured data with Google’s Rich Results Test before launch.</p></section>
@endsection
