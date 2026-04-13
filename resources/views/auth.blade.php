<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phanda Account</title>
    @include('partials.ui.favicons')
    <style>
        :root {
            --phanda-orange: #ff6a00;
            --phanda-orange-soft: #ff8b3d;
            --phanda-dark: #0b0b0b;
            --phanda-muted: #475569;
            --phanda-bg: #f8fafc;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--phanda-dark);
            background:
                radial-gradient(circle at 12% 10%, rgba(255,106,0,.14), transparent 35%),
                radial-gradient(circle at 85% 80%, rgba(255,106,0,.10), transparent 40%),
                var(--phanda-bg);
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .auth-shell {
            width: 100%;
            max-width: 980px;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            border-radius: 22px;
            overflow: hidden;
            background: #fff;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 24px 60px rgba(2, 6, 23, .14);
        }
        .left {
            background: linear-gradient(150deg, #111827, #0f172a 70%);
            color: #fff;
            padding: 44px 36px;
        }
        .pill {
            display: inline-flex;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255,106,0,.17);
            color: #ffd8bf;
            font-size: 12px;
            font-weight: 700;
        }
        .left h1 {
            margin: 14px 0 8px;
            font-size: 34px;
            line-height: 1.1;
        }
        .left p {
            margin: 0;
            color: rgba(255,255,255,.78);
            line-height: 1.55;
            max-width: 360px;
        }
        .right {
            padding: 36px 32px;
        }
        .tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 6px;
            margin-bottom: 18px;
        }
        .tab-btn {
            border: 0;
            background: transparent;
            border-radius: 9px;
            padding: 10px;
            font-size: 14px;
            font-weight: 700;
            color: #334155;
            cursor: pointer;
        }
        .tab-btn.active {
            background: #fff;
            color: #c2410c;
            box-shadow: 0 8px 16px rgba(2, 6, 23, .08);
        }
        .panel { display: none; }
        .panel.active { display: block; }
        .title {
            margin: 0;
            font-size: 28px;
        }
        .sub {
            margin: 7px 0 18px;
            color: var(--phanda-muted);
            font-size: 14px;
        }
        .field { margin-bottom: 12px; }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }
        input, select {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 11px 13px;
            font-size: 14px;
            background: #fff;
        }
        input:focus, select:focus {
            outline: none;
            border-color: var(--phanda-orange);
            box-shadow: 0 0 0 4px rgba(255,106,0,.12);
        }
        .btn-primary {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 12px;
            font-size: 14px;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, var(--phanda-orange), var(--phanda-orange-soft));
            box-shadow: 0 12px 24px rgba(255,106,0,.24);
            cursor: pointer;
            margin-top: 8px;
        }
        .alert {
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 12px;
            font-size: 13px;
            border: 1px solid;
        }
        .alert-error { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        .alert-success { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
        .alert ul { margin: 0; padding-left: 18px; }
        @media (max-width: 900px) {
            .auth-shell { grid-template-columns: 1fr; }
            .left { padding: 28px 22px; }
            .right { padding: 26px 22px; }
            .left h1 { font-size: 29px; }
        }
        @media (max-width: 650px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <section class="left">
            <span class="pill">PHANDA PORTAL</span>
            <h1>One account page for everyone</h1>
            <p>Sign in or create an account as a customer or provider from this shared page.</p>
        </section>

        <section class="right">
            <div class="tabs">
                <button type="button" class="tab-btn active" data-tab="loginPanel">Sign In</button>
                <button type="button" class="tab-btn" data-tab="signupPanel">Sign Up</button>
            </div>

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="loginPanel" class="panel active">
                <h2 class="title">Sign In</h2>
                <p class="sub">Use your email and password to continue.</p>
                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf
                    <div class="field">
                        <label for="login_email">Email</label>
                        <input id="login_email" type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="field">
                        <label for="login_password">Password</label>
                        <input id="login_password" type="password" name="password" required>
                    </div>
                    <button type="submit" class="btn-primary">Sign In</button>
                </form>
            </div>

            <div id="signupPanel" class="panel">
                <h2 class="title">Create Account</h2>
                <p class="sub">Choose your role and create your account.</p>
                <form method="POST" action="{{ route('signup.submit') }}">
                    @csrf
                    <div class="grid">
                        <div class="field">
                            <label for="full_name">Full name</label>
                            <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required>
                        </div>
                        <div class="field">
                            <label for="phone">Phone</label>
                            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required>
                        </div>
                    </div>
                    <div class="field">
                        <label for="signup_email">Email</label>
                        <input id="signup_email" type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="field">
                        <label for="role">Account type</label>
                        <select id="role" name="role" required>
                            <option value="customer" {{ old('role') === 'customer' ? 'selected' : '' }}>Customer</option>
                            <option value="provider" {{ old('role') === 'provider' ? 'selected' : '' }}>Provider</option>
                        </select>
                    </div>
                    <div class="grid">
                        <div class="field">
                            <label for="password">Password</label>
                            <input id="password" type="password" name="password" required>
                        </div>
                        <div class="field">
                            <label for="password_confirmation">Confirm password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Create Account</button>
                </form>
            </div>
        </section>
    </div>

    <script>
        const tabButtons = document.querySelectorAll('.tab-btn');
        const panels = document.querySelectorAll('.panel');
        tabButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                tabButtons.forEach((b) => b.classList.remove('active'));
                panels.forEach((p) => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(btn.dataset.tab).classList.add('active');
            });
        });

        @if($errors->any() || old('full_name') || old('phone') || old('role'))
            document.querySelector('[data-tab="signupPanel"]').click();
        @endif
    </script>
</body>
</html>
