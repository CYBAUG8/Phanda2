<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phanda · log in</title>
    @include('partials.ui.favicons')
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* subtle orange pattern */
        .bg-light-pattern {
            background-color: #ffffff;
            background-image: radial-gradient(rgba(249, 115, 22, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }
    </style>
</head>
<body class="bg-light-pattern font-sans antialiased flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md">
        <!-- logo linking to landing page -->
        <a href="/" class="block text-center mb-6 group">
            <span class="text-3xl font-bold text-orange-600 group-hover:text-orange-700 transition">Phanda</span>
            <span class="block text-xs text-black/40 tracking-wider mt-1">where users meet providers</span>
        </a>

        <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl shadow-orange-900/5 border border-black/10 p-8 md:p-10">
            <h1 class="text-2xl font-bold text-gray-800 mb-2 text-center">Welcome back</h1>
            <p class="text-sm text-black/40 text-center mb-8">log in to your dashboard</p>

            @if(session('error'))
                <div class="mb-6 bg-orange-50 border-l-4 border-orange-500 text-orange-700 px-4 py-3 rounded-r-lg text-sm flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <i class="far fa-envelope mr-1 text-orange-400"></i> Email
                    </label>
                    <input type="email" name="email" required
                           class="w-full border border-black/20 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-300 transition bg-white/70"
                           placeholder="name@example.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <i class="fas fa-lock mr-1 text-orange-400"></i> Password
                    </label>
                    <input type="password" name="password" required
                           class="w-full border border-black/20 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-300 transition bg-white/70"
                           placeholder="••••••••">
                </div>

                <!-- remember & forgot -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-black/40">
                        <input type="checkbox" class="rounded border-black/30 text-orange-600 focus:ring-orange-500">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="text-orange-600 hover:text-orange-800 font-medium transition">Forgot password?</a>
                </div>

                <button type="submit"
                        class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3.5 rounded-xl transition duration-200 shadow-md shadow-orange-600/30 flex items-center justify-center gap-2 text-base">
                    <i class="fas fa-arrow-right-to-bracket"></i> Log in
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-black/40 border-t border-black/10 pt-6">
                <span>New to Phanda?</span>
                <a href="/register" class="ml-1 text-orange-600 hover:text-orange-800 font-semibold hover:underline transition">
                    Create an account <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>

            <!-- user/provider hint -->
            <div class="mt-4 flex justify-center gap-3 text-xs text-black/40">
                <span class="flex items-center gap-1"><i class="fas fa-user-astronaut text-orange-300"></i> user</span>
                <span class="flex items-center gap-1"><i class="fas fa-rocket text-orange-300"></i> provider</span>
            </div>
        </div>

        <p class="text-center text-xs text-black/40 mt-6">© 2026 Phanda – secure, simple connection</p>
    </div>

</body>
</html>
