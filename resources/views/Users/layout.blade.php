<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Phanda User</title>

    {{-- FontAwesome CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Load Vite JS and users CSS (reuses firstpage entry) --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/firstpage.js', 'resources/css/users.css'])
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
                    <span>Phanda User</span>
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
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="flash-message flash-message--success" id="flashMessage">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="flash-message flash-message--error" id="flashMessage">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')

    <script>
        // Auto-dismiss flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flash = document.getElementById('flashMessage');
            if (flash) {
                setTimeout(() => {
                    flash.style.opacity = '0';
                    flash.style.transform = 'translateY(-10px)';
                    setTimeout(() => flash.remove(), 300);
                }, 5000);
            }
        });
    </script>
</body>
</html>
