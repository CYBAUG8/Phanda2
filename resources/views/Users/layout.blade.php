<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phanda User</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/firstpage.js', 'resources/css/users.css','resources/css/dashboard.css'])
    @else
        <link rel="stylesheet" href="/build/assets/users.css">
        <script src="/build/assets/firstpage.js"></script>
    @endif
</head>
<body>
<div class="dashboard-container">
    <header class="header">
        <div class="header-content">
            <a href="{{ route('users.dashboard') }}" class="logo"><div class="logo-icon"></div><span>Phanda User</span></a>
            <div class="user-info"><div class="avatar">{{ strtoupper(substr(auth()->user()->full_name ?? 'US', 0, 2)) }}</div></div>
        </div>
    </header>

    <aside class="sidebar">
        <nav class="sidebar-nav">
            <a href="{{ route('users.dashboard') }}" class="sidebar-item @if(request()->is('users/dashboard')) active @endif"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="{{ route('users.services') }}" class="sidebar-item @if(request()->is('users/services')) active @endif"><i class="fas fa-search"></i><span>Find Services</span></a>
            <a href="{{ route('users.bookings') }}" class="sidebar-item @if(request()->is('users/bookings')) active @endif"><i class="fas fa-calendar-check"></i><span>My Bookings</span></a>
            <a href="{{ route('user.messages') }}" class="sidebar-item @if(request()->is('users/messages*')) active @endif"><i class="fas fa-comments"></i><span>Messages</span></a>
            <a href="{{ route('reviews.reviews') }}" class="sidebar-item @if(request()->is('users/reviews')) active @endif"><i class="fas fa-star"></i><span>Reviews</span></a>
            <a href="{{ route('users.profile') }}" class="sidebar-item @if(request()->is('users/profile')) active @endif"><i class="fas fa-user"></i><span>Profile</span></a>
            <a href="{{ route('users.settings') }}" class="sidebar-item @if(request()->is('users/settings')) active @endif"><i class="fas fa-cog"></i><span>Settings</span></a>
        </nav>

        <div class="sidebar-item mt-auto">
            <form method="POST" action="{{ route('logout') }}" class="w-100">@csrf
                <button class="logout-link w-100" type="submit"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
            </form>
        </div>
    </aside>

    <main class="user-content">@yield('content')</main>
</div>
@stack('scripts')
</body>
</html>
