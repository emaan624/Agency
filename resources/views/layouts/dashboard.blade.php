<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground flex min-h-screen">

{{-- Sidebar --}}
<aside class="w-64 bg-surface border-r border-border flex-shrink-0 flex flex-col">
    <div class="p-6 border-b border-border">
        <a href="{{ route('home') }}" class="text-xl font-bold text-primary">LuxMotion</a>
    </div>
    <nav class="flex-1 p-4 space-y-1">
        <a href="{{ route('dashboard.index') }}" class="sidebar-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">📊 Dashboard</a>
        <a href="{{ route('dashboard.orders.index') }}" class="sidebar-link {{ request()->routeIs('dashboard.orders.*') ? 'active' : '' }}">📦 Orders</a>
        <a href="{{ route('dashboard.wallet.index') }}" class="sidebar-link {{ request()->routeIs('dashboard.wallet.*') ? 'active' : '' }}">💰 Wallet</a>
        <a href="{{ route('dashboard.tickets.index') }}" class="sidebar-link {{ request()->routeIs('dashboard.tickets.*') ? 'active' : '' }}">🎫 Tickets</a>
        <a href="{{ route('dashboard.notifications.index') }}" class="sidebar-link {{ request()->routeIs('dashboard.notifications.*') ? 'active' : '' }}">🔔 Notifications</a>
        <a href="{{ route('dashboard.profile.edit') }}" class="sidebar-link {{ request()->routeIs('dashboard.profile.*') ? 'active' : '' }}">👤 Profile</a>
    </nav>
    <div class="p-4 border-t border-border">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-link w-full text-left">🚪 Logout</button>
        </form>
    </div>
</aside>

{{-- Main --}}
<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-surface border-b border-border px-8 py-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold">@yield('page-title', 'Dashboard')</h1>
        <div class="flex items-center gap-4 text-sm text-muted">
            <span>{{ auth()->user()->name }}</span>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-8">
        @if(session('success'))
            <div class="alert alert-success mb-6">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error mb-6">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</div>

<style>
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: var(--color-muted);
        transition: all 0.15s;
        text-decoration: none;
    }
    .sidebar-link:hover, .sidebar-link.active {
        background-color: var(--color-primary);
        color: var(--color-background);
        opacity: 1;
    }
</style>

</body>
</html>
