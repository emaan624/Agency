<!DOCTYPE html>
<html lang="en" data-theme="{{ Setting::get('theme', 'dark') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', Setting::get('site_name', 'LuxMotion Agency'))</title>
    <meta name="description" content="@yield('meta_description', Setting::get('site_tagline', 'Premium Creative Services'))">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground min-h-screen flex flex-col">

{{-- Navbar --}}
<nav class="navbar sticky top-0 z-50 border-b border-border bg-surface px-6 py-4">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <a href="{{ route('home') }}" class="text-2xl font-bold text-primary">
            {{ Setting::get('site_name', 'LuxMotion') }}
        </a>
        <div class="hidden md:flex items-center gap-8">
            <a href="{{ route('home') }}" class="nav-link">Home</a>
            <a href="{{ route('pricing') }}" class="nav-link">Pricing</a>
            <a href="{{ route('portfolio') }}" class="nav-link">Portfolio</a>
            <a href="{{ route('about') }}" class="nav-link">About</a>
            <a href="{{ route('contact') }}" class="nav-link">Contact</a>
        </div>
        <div class="flex items-center gap-4">
            @auth
                <a href="{{ route('dashboard.index') }}" class="btn btn-primary">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="nav-link">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
            @endauth
        </div>
    </div>
</nav>

{{-- Main content --}}
<main class="flex-1">
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="alert alert-error">{{ session('error') }}</div>
        </div>
    @endif
    @yield('content')
</main>

{{-- Footer --}}
<footer class="bg-surface border-t border-border px-6 py-12 mt-16">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
            <h3 class="text-xl font-bold text-primary mb-4">{{ Setting::get('site_name', 'LuxMotion') }}</h3>
            <p class="text-muted text-sm">{{ Setting::get('site_tagline', 'Premium Creative Services') }}</p>
        </div>
        <div>
            <h4 class="font-semibold mb-3">Services</h4>
            <ul class="space-y-2 text-sm text-muted">
                <li><a href="{{ route('pricing') }}" class="hover:text-primary">Packages</a></li>
                <li><a href="{{ route('portfolio') }}" class="hover:text-primary">Portfolio</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-semibold mb-3">Company</h4>
            <ul class="space-y-2 text-sm text-muted">
                <li><a href="{{ route('about') }}" class="hover:text-primary">About Us</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-primary">Contact</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-semibold mb-3">Contact</h4>
            <ul class="space-y-2 text-sm text-muted">
                <li>{{ Setting::get('site_email', '') }}</li>
                <li>{{ Setting::get('site_phone', '') }}</li>
            </ul>
        </div>
    </div>
    <div class="max-w-7xl mx-auto border-t border-border mt-8 pt-6 text-center text-sm text-muted">
        &copy; {{ date('Y') }} {{ Setting::get('site_name', 'LuxMotion Agency') }}. All rights reserved.
    </div>
</footer>

</body>
</html>
