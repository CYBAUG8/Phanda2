<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phanda Provider</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/firstpage.js', 'resources/css/providers.css'])
    @else
        <link rel="stylesheet" href="/build/assets/providers.css">
        <script src="/build/assets/firstpage.js"></script>
    @endif

    @stack('styles')
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body>
<div class="dashboard-container">
    <header class="header">
        <div class="header-content">
            <a href="{{ route('providers.dashboard') }}" class="logo">
                <div class="logo-icon"></div>
                <span>Phanda Provider</span>
            </a>
            <div class="user-info"><div class="avatar">{{ strtoupper(substr(auth()->user()->full_name ?? 'PR', 0, 2)) }}</div></div>
        </div>
    </header>

    <aside class="sidebar">
        <nav class="sidebar-nav">
            <a href="{{ route('providers.dashboard') }}" class="sidebar-item @if(request()->is('providers/dashboard')) active @endif"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="{{ route('provider.services.index') }}" class="sidebar-item @if(request()->is('providers/services')) active @endif"><i class="fas fa-concierge-bell"></i><span>Services</span></a>
            <a href="{{ route('provider.bookings') }}" class="sidebar-item @if(request()->is('providers/bookings')) active @endif"><i class="fas fa-clipboard-list"></i><span>Bookings</span></a>
            <a href="{{ route('provider.schedule') }}" class="sidebar-item @if(request()->is('providers/schedule') || request()->is('provider/calendar*')) active @endif"><i class="fas fa-calendar-alt"></i><span>Schedule</span></a>
            <a href="{{ route('provider.messages') }}" class="sidebar-item @if(request()->is('providers/messages*')) active @endif"><i class="fas fa-comments"></i><span>Messages</span></a>
            <a href="{{ route('provider.earnings') }}" class="sidebar-item @if(request()->is('providers/earnings')) active @endif"><i class="fas fa-wallet"></i><span>Earnings</span></a>
            <a href="{{ route('provider.profile') }}" class="sidebar-item @if(request()->is('providers/profile')) active @endif"><i class="fas fa-user"></i><span>Profile</span></a>
        </nav>

        <div class="sidebar-item mt-auto">
            <form method="POST" action="{{ route('logout') }}" class="w-100">
                @csrf
                <button class="logout-link w-100" type="submit"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
            </form>
        </div>
    </aside>

    <main class="provider-content">@yield('content')</main>
</div>

@stack('scripts')
</body>
</html>
