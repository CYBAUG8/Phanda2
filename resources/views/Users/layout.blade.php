<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panda User</title>

    {{-- Load Vite JS and users CSS (reuses firstpage entry) --}}
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
                <a href="/users/dashboard" class="logo">
                    <div class="logo-icon"></div>
                    <span>Panda User</span>
                </a>
                <div class="user-info">
                    <div class="location-badge">
                        <i class="fas fa-map-marker-alt"></i>
                        <span></span>
                    </div>
                    <div class="avatar">JD</div>
                </div>
            </div>
        </header>

        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="/users/dashboard" class="sidebar-item @if(request()->is('users/dashboard')) active @endif">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/users/services" class="sidebar-item @if(request()->is('users/services')) active @endif">
                    <i class="fas fa-search"></i>
                    <span>Find Services</span>
                </a>
                <a href="/users/bookings" class="sidebar-item @if(request()->is('users/bookings')) active @endif">
                    <i class="fas fa-calendar-check"></i>
                    <span>My Bookings</span>
                </a>
                <a href="/users/messages" class="sidebar-item @if(request()->is('users/messages')) active @endif">
                    <i class="fas fa-comments"></i>
                    <span>Messages</span>
                </a>
                <a href="/users/reviews" class="sidebar-item @if(request()->is('users/reviews')) active @endif">
                    <i class="fas fa-star"></i>
                    <span>Reviews</span>
                </a>
                <a href="/users/profile" class="sidebar-item @if(request()->is('users/profile')) active @endif">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
                <a href="/users/settings" class="sidebar-item @if(request()->is('users/settings')) active @endif">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>

        <main class="user-content">
            @yield('content')
        </main>
    </div>
</body>
</html>
