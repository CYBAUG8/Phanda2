<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>Phanda · log in</title>
    <!-- Tailwind + Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
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

        <p class="text-center text-xs text-black/40 mt-6">© 2025 Phanda – secure, simple connection</p>
=======
    <title>Phanda Auth</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --brand:#ff6a00; --ink:#101828; --muted:#667085; --line:#eaecf0; --bg:#f8fafc; }
        * { box-sizing:border-box; font-family: "Segoe UI", system-ui, sans-serif; }
        body { margin:0; min-height:100vh; background: radial-gradient(circle at 10% 20%, #ffe7d6 0%, #f8fafc 35%, #f5f7ff 100%); display:grid; place-items:center; color:var(--ink); }
        .auth { width:min(920px, 94vw); display:grid; grid-template-columns: 1fr 1fr; background:#fff; border:1px solid var(--line); border-radius:22px; overflow:hidden; box-shadow:0 30px 80px rgba(16,24,40,.12); }
        .brand { background:linear-gradient(145deg,#111827,#0b0f19); color:#fff; padding:42px 34px; position:relative; }
        .brand h1 { margin:0 0 10px; font-size:34px; letter-spacing:.4px; }
        .brand p { margin:0; color:rgba(255,255,255,.78); line-height:1.5; }
        .brand .dot { width:12px; height:12px; border-radius:999px; background:var(--brand); margin-top:26px; box-shadow:0 0 0 10px rgba(255,106,0,.2); }
        .panel { padding:28px 28px 24px; }
        .tabs { display:flex; gap:8px; margin-bottom:18px; }
        .tab { border:1px solid var(--line); background:#fff; color:var(--muted); padding:8px 12px; border-radius:999px; font-weight:600; cursor:pointer; }
        .tab.active { background:var(--brand); color:#fff; border-color:var(--brand); }
        .form { display:none; }
        .form.active { display:block; }
        label { display:block; font-size:13px; font-weight:600; margin:10px 0 6px; }
        input, select { width:100%; padding:11px 12px; border:1px solid var(--line); border-radius:10px; font-size:14px; }
        input:focus, select:focus { outline:none; border-color:var(--brand); box-shadow:0 0 0 4px rgba(255,106,0,.12); }
        .btn { width:100%; border:none; background:var(--brand); color:#fff; padding:11px 12px; border-radius:10px; font-weight:700; cursor:pointer; margin-top:14px; }
        .error { background:#fef2f2; color:#b42318; border:1px solid #fecaca; padding:10px; border-radius:10px; font-size:13px; margin-bottom:10px; }
        .hint { color:var(--muted); font-size:12px; margin-top:8px; }
        @media (max-width: 860px) { .auth { grid-template-columns: 1fr; } .brand { padding:26px; } }
    </style>
</head>
<body>
<div class="auth">
    <div class="brand">
        <h1>Phanda</h1>
        <p>One authentication portal for both customers and providers.</p>
        <div class="dot"></div>
>>>>>>> services-bookings-feature
    </div>

    <div class="panel">
        <div class="tabs">
            <button class="tab active" type="button" data-target="loginForm">Login</button>
            <button class="tab" type="button" data-target="signupForm">Sign Up</button>
        </div>

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <form id="loginForm" class="form active" method="POST" action="{{ route('login.submit') }}">
            @csrf
            <label>Email</label>
            <input type="email" name="email" required value="{{ old('email') }}">
            <label>Password</label>
            <input type="password" name="password" required>
            <button class="btn" type="submit">Login</button>
        </form>

        <form id="signupForm" class="form" method="POST" action="{{ route('register.submit') }}">
            @csrf
            <label>Full Name</label>
            <input type="text" name="full_name" required>
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Phone</label>
            <input type="text" name="phone">
            <label>Account Type</label>
            <select name="role" required>
                <option value="customer">User (Customer)</option>
                <option value="provider">Provider</option>
            </select>
            <label>Password</label>
            <input type="password" name="password" required>
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" required>
            <button class="btn" type="submit">Create Account</button>
            <div class="hint">Providers and users share this same sign in/up page.</div>
        </form>
    </div>
</div>
<script>
const tabs = document.querySelectorAll('.tab');
const forms = document.querySelectorAll('.form');
tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        forms.forEach(f => f.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.target).classList.add('active');
    });
});
</script>
</body>
</html>