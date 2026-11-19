<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f5fa">
    <title>Admin sign in | Home Movers and Cleaners</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <main class="login-shell">
        <form class="login-card" method="post" action="{{ route('admin.authenticate') }}">
            @csrf
            <div class="login-heading">
                <div>
                    <div class="eyebrow">ADMIN ACCESS</div>
                    <h1>Home Movers<br>and Cleaners</h1>
                    <p>Sign in to manage bookings and website details.</p>
                </div>
                <span class="login-avatar" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5.5 20a6.5 6.5 0 0 1 13 0"/></svg>
                </span>
            </div>

            @include('partials.errors')

            <label class="login-field" for="admin-email">
                <span>Email</span>
                <span class="login-control">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>
                    <input id="admin-email" type="email" name="email" required value="{{ old('email') }}" autocomplete="username" autofocus>
                </span>
            </label>

            <label class="login-field" for="admin-password">
                <span>Password</span>
                <span class="login-control">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3m-4 5v2"/></svg>
                    <input id="admin-password" type="password" name="password" required autocomplete="current-password">
                    <button class="login-password-toggle" type="button" data-password-toggle aria-label="Show password" aria-pressed="false">Show</button>
                </span>
            </label>

            <div class="login-options">
                <label class="login-remember"><input type="checkbox" name="remember" value="1"> <span>Remember me</span></label>
                <a href="{{ route('home') }}">Back to site</a>
            </div>

            <button class="button login-submit" type="submit">Log in</button>
            <p class="login-note">Admin access is restricted to authorized team members. If you need access, contact your site administrator.</p>
        </form>
    </main>
</body>
</html>
