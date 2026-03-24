<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Provider Login - Phanda</title>
    @include('partials.ui.favicons')
    @vite(['resources/css/app.css'])
</head>
<body class="grid min-h-screen place-items-center bg-slate-50 px-4 py-10 text-slate-900">
    <div class="w-full max-w-md">
        <a href="/" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-slate-600 no-underline hover:text-slate-900">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 text-orange-600">P</span>
            <span>Phanda</span>
        </a>

        <div class="ui-card p-5 sm:p-6">
            <h1 class="text-xl font-semibold text-slate-900">Provider Sign in</h1>
            <p class="mt-1 text-sm text-slate-500">Sign in with your provider account to access the dashboard.</p>

            @if(session('error'))
                <div class="ui-alert ui-alert-error mt-4">
                    {{ session('error') }}
                </div>
            @endif

            <form method="GET" action="/provider/enter" class="mt-5 space-y-4">
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" required placeholder="provider@example.com" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </div>
                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required placeholder="secret" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </div>
                <button class="ui-btn-primary w-full justify-center" type="submit">Sign in</button>
            </form>
        </div>
    </div>
</body>
</html>
