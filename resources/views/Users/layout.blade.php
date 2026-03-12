<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panda User</title>
       <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <script src="https://unpkg.com/@heroicons/react@24/outline/index.js"></script>
 
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    {{-- FontAwesome CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Load Vite JS and users CSS (reuses firstpage entry) --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/firstpage.js', 'resources/css/users.css','resources/css/dashboard.css'])
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @else
        <link rel="stylesheet" href="/build/assets/users.css">
        <script src="/build/assets/firstpage.js"></script>
    @endif
    
        
</head>
<body>
    @php
        $userName = trim((string) (auth()->user()?->full_name ?? 'User'));
        $nameParts = preg_split('/\s+/', $userName) ?: [];
        $initials = collect($nameParts)
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
    @endphp

    <div class="dashboard-container">
        <header class="header">
            <div class="header-content">
                <a href="/users/dashboard" class="logo">
                    <div class="logo-icon"></div>
                    <span>Panda User</span>
                </a>
                <div class="user-info">
                    <div class="avatar">{{ $initials !== '' ? $initials : 'U' }}</div>
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
            <div class="sidebar-item mt-auto">
                <a href="/logout" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <main class="user-content">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
