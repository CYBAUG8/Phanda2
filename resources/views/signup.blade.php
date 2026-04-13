<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up - Phanda</title>
    @include('partials.ui.favicons')
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 px-4 py-10 text-slate-900 sm:px-6">
    <div class="mx-auto w-full max-w-2xl">
        <a href="/" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 no-underline hover:text-slate-900">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 text-orange-600">P</span>
            <span>Phanda</span>
        </a>

        <div class="ui-card mt-4 p-5 sm:p-7">
            <h1 class="text-2xl font-semibold text-slate-900">Create Account</h1>
            <p class="mt-1 text-sm text-slate-500">Set up your customer account to start booking services.</p>

            @if($errors->any())
                <div class="ui-alert ui-alert-error mt-5">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('signup.submit') }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="role" value="customer">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="full_name" class="mb-1 block text-sm font-medium text-slate-700">Full name</label>
                        <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </div>

                    <div>
                        <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">Phone</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </div>
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                        <input id="password" type="password" name="password" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirm password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    </div>
                </div>

                <button type="submit" class="ui-btn-primary w-full justify-center">
                    Create Account
                </button>
            </form>

            <p class="mt-4 text-sm text-slate-600">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-orange-600 no-underline hover:text-orange-700">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
