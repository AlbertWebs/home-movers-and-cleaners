<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · Home Movers</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        @php
            $adminHomeRoute = auth()->user()->is_admin
                ? route('admin.dashboard')
                : (auth()->user()->role === 'booking_manager' ? route('admin.bookings') : route('admin.homepage'));
        @endphp
        <a class="admin-brand" href="{{ $adminHomeRoute }}"><img src="{{ asset('images/brand-logo.png') }}" alt=""><span>STUDIO<br><small>WEBSITE MANAGER</small></span></a>
        <span class="sidebar-label">WORKSPACE</span>
        @can('manage-settings')<a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i>◫</i> Overview</a>@endcan
        @can('manage-bookings')
            <a href="{{ route('admin.bookings') }}" class="{{ request()->routeIs('admin.bookings*') ? 'active' : '' }}"><i>↗</i> Bookings</a>
            <a href="{{ route('admin.enquiries') }}" class="{{ request()->routeIs('admin.enquiries*') ? 'active' : '' }}"><i>✉</i> Enquiries</a>
        @endcan
        @can('manage-content')
            <span class="sidebar-label">WEBSITE</span>
            <a href="{{ route('admin.homepage') }}" class="{{ request()->routeIs('admin.homepage*') ? 'active' : '' }}"><i>⌂</i> Homepage</a>
            <a href="{{ route('admin.pages') }}" class="{{ request()->routeIs('admin.pages*') ? 'active' : '' }}"><i>▤</i> Pages and policies</a>
            <a href="{{ route('admin.pages', ['edit' => 'terms']) }}" class="{{ request()->routeIs('admin.pages*') && request('edit') === 'terms' ? 'active' : '' }}"><i>§</i> Terms of Service</a>
            <a href="{{ route('admin.services') }}" class="{{ request()->routeIs('admin.services*') ? 'active' : '' }}"><i>⌂</i> Services</a>
            <a href="{{ route('admin.pricing') }}" class="{{ request()->routeIs('admin.pricing*') ? 'active' : '' }}"><i>＄</i> Pricing</a>
            <a href="{{ route('admin.gallery') }}" class="{{ request()->routeIs('admin.gallery*') ? 'active' : '' }}"><i>▦</i> Gallery</a>
            <a href="{{ route('admin.testimonials') }}" class="{{ request()->routeIs('admin.testimonials*') ? 'active' : '' }}"><i>♡</i> Testimonials</a>
            <a href="{{ route('admin.media') }}" class="{{ request()->routeIs('admin.media*') ? 'active' : '' }}"><i>▧</i> Media library</a>
            <a href="{{ route('admin.blog.index') }}" class="{{ request()->routeIs('admin.blog.*') ? 'active' : '' }}"><i>▤</i> Resource centre</a>
        @endcan
        @can('manage-settings')
            <a href="{{ route('admin.seo') }}" class="{{ request()->routeIs('admin.seo*') ? 'active' : '' }}"><i>⌕</i> SEO and redirects</a>
            <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}"><i>⚙</i> Settings</a>
            <a href="{{ route('admin.staff') }}" class="{{ request()->routeIs('admin.staff*') ? 'active' : '' }}"><i>♙</i> Staff access</a>
        @endcan
        <div class="sidebar-bottom">
            <a href="{{ route('home') }}" target="_blank" rel="noopener"><i>↗</i> View website</a>
            <form method="post" action="{{ route('admin.logout') }}">@csrf<button><i>⇥</i> Sign out</button></form>
        </div>
    </aside>
    <main class="admin-main">
        <header class="admin-top">
            <div><span>HOME MOVERS &amp; CLEANERS</span><strong>@yield('title', 'Overview')</strong></div>
            <div class="admin-user"><span>{{ auth()->user()->name }}</span><b>{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</b></div>
        </header>
        <div class="admin-content">
            @if(session('success'))<div class="notice" role="status">{{ session('success') }}</div>@endif
            @include('partials.errors')
            @yield('content')
        </div>
    </main>
</body>
</html>
