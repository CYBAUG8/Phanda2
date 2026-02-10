<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phanda Provider</title>

    {{-- Load Vite JS and providers CSS (reuses firstpage entry) --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/firstpage.js', 'resources/css/providers.css'])
    @else
        <link rel="stylesheet" href="/build/assets/providers.css">
        <script src="/build/assets/firstpage.js"></script>
    @endif
</head>
<body>
    <div class="dashboard-container">
        <header class="header">
            <div class="header-content">
                <a href="/providers/dashboard" class="logo">
                    <div class="logo-icon"></div>
                    <span>Phanda Provider</span>
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
                <a href="/providers/dashboard" class="sidebar-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/providers/services" class="sidebar-item">
                    <i class="fas fa-concierge-bell"></i>
                    <span>Services</span>
                </a>
                <a href="/providers/schedule" class="sidebar-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule</span>
                </a>
                <a href="/providers/bookings" class="sidebar-item @if(request()->is('providers/bookings')) active @endif">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Bookings</span>
                </a>
                <a href="/providers/earnings" class="sidebar-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Earnings</span>
                </a>
                <a href="/providers/messages" class="sidebar-item">
                    <i class="fas fa-comments"></i>
                    <span>Messages</span>
                </a>
                <a href="/providers/profile" class="sidebar-item">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </nav>
        </aside>

        <main class="provider-content">
            @yield('content')
        </main>
    </div>
</body>
</html>
