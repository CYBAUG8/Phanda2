<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Phanda Provider</title>
    @include('partials.ui.favicons')

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/css/providers.css', 'resources/js/firstpage.js'])

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    @stack('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    @php
        $providerName = trim((string) (auth()->user()?->full_name ?? 'Provider'));
        $nameParts = preg_split('/\s+/', $providerName) ?: [];
        $initials = collect($nameParts)
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');

        $navItems = [
            ['href' => '/providers/dashboard', 'label' => 'Dashboard', 'icon' => 'fa-house', 'active' => request()->is('providers/dashboard')],
            ['href' => '/providers/services', 'label' => 'Services', 'icon' => 'fa-concierge-bell', 'active' => request()->is('providers/services*')],
            ['href' => '/providers/schedule', 'label' => 'Schedule', 'icon' => 'fa-calendar-days', 'active' => request()->is('providers/schedule') || request()->is('provider/calendar*')],
            ['href' => '/providers/bookings', 'label' => 'Bookings', 'icon' => 'fa-clipboard-list', 'active' => request()->is('providers/bookings*')],
            ['href' => '/providers/earnings', 'label' => 'Earnings', 'icon' => 'fa-chart-line', 'active' => request()->is('providers/earnings*')],
            ['href' => '/providers/messages', 'label' => 'Messages', 'icon' => 'fa-comments', 'active' => request()->is('providers/messages*')],
            ['href' => '/providers/profile', 'label' => 'Profile', 'icon' => 'fa-user', 'active' => request()->is('providers/profile')],
        ];
    @endphp

    <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
        <aside class="hidden border-r border-slate-200 bg-white lg:flex lg:flex-col">
            <div class="border-b border-slate-200 px-5 py-4">
                <a href="/providers/dashboard" class="flex items-center gap-3 no-underline group">
                    <div class="w-9 h-9 rounded-xl bg-orange-500 text-white flex items-center justify-center font-bold text-xl shadow-sm transition-transform group-hover:scale-105">
                        P
                    </div>
                    <span class="font-bold text-gray-900 text-xl tracking-tight">
                        Phanda
                    </span>
                </a>
            </div>

            <nav class="flex-1 space-y-1 p-4">
                @foreach($navItems as $item)
                    <a href="{{ $item['href'] }}" @class(['ui-nav-link', 'ui-nav-link-active' => $item['active']])>
                        <i class="fa-solid {{ $item['icon'] }} w-4 text-center"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-slate-200 p-4">
                <a href="/logout" class="ui-btn-secondary w-full justify-center">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <div class="flex min-h-screen flex-col">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div class="flex items-center justify-between px-4 py-3 sm:px-6">
                    <a href="/providers/dashboard" class="group flex items-center gap-2 text-slate-900 no-underline lg:hidden">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500 text-base font-bold text-white shadow-sm transition-transform group-hover:scale-105">
                            P
                        </div>
                        <span class="text-lg font-bold tracking-tight">Phanda</span>
                    </a>
                    <p class="hidden text-xs font-medium uppercase tracking-widest text-slate-500 lg:block">Provider Portal</p>

                    <div class="flex items-center gap-3">
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-semibold text-slate-900">{{ $providerName }}</p>
                            <p class="text-xs text-slate-500">Provider</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-orange-500 text-sm font-semibold text-white">
                            {{ $initials !== '' ? $initials : 'PR' }}
                        </div>
                    </div>
                </div>

                <nav class="overflow-x-auto border-t border-slate-200 px-2 py-2 lg:hidden">
                    <div class="flex min-w-max items-center gap-1">
                        @foreach($navItems as $item)
                            <a href="{{ $item['href'] }}" @class([
                                'min-h-11 rounded-lg px-4 py-2.5 text-sm font-medium no-underline',
                                'bg-orange-50 text-orange-700' => $item['active'],
                                'text-slate-600 hover:bg-slate-100' => !$item['active'],
                            ])>
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                        <a href="/logout" class="min-h-11 rounded-lg px-4 py-2.5 text-sm font-medium text-slate-600 no-underline hover:bg-slate-100">
                            Logout
                        </a>
                    </div>
                </nav>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.ui.feedback')
    @stack('scripts')
</body>
</html>
