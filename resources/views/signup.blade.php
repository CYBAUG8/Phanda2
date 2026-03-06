<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up - Phanda</title>
    <style>
        :root {
            --phanda-orange: #ff6a00;
            --phanda-orange-soft: #ff8b3d;
            --phanda-dark: #0b0b0b;
            --phanda-slate: #475569;
            --phanda-bg: #f8fafc;
            --phanda-card: #ffffff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--phanda-dark);
            background:
                radial-gradient(circle at 10% 10%, rgba(255,106,0,.14), transparent 40%),
                radial-gradient(circle at 88% 80%, rgba(255,106,0,.1), transparent 35%),
                var(--phanda-bg);
            padding: 24px;
        }
        .auth-card {
            width: 100%;
            max-width: 640px;
            background: var(--phanda-card);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 24px 60px rgba(2, 6, 23, 0.12);
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        .title {
            margin: 0;
            font-size: 30px;
        }
        .sub {
            margin: 8px 0 20px;
            color: var(--phanda-slate);
            font-size: 14px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .field {
            margin-bottom: 14px;
        }
        label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }
        input, select {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 12px 14px;
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
            padding: 12px 14px;
            font-weight: 800;
            font-size: 14px;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(135deg, var(--phanda-orange), var(--phanda-orange-soft));
            box-shadow: 0 12px 24px rgba(255,106,0,.26);
            margin-top: 10px;
        }
        .helper {
            margin-top: 14px;
            font-size: 14px;
            color: #475569;
        }
        .helper a {
            color: #c2410c;
            font-weight: 700;
            text-decoration: none;
        }
        .alert {
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 14px;
            font-size: 13px;
            border: 1px solid;
            background: #fef2f2;
            border-color: #fecaca;
            color: #b91c1c;
        }
        .alert ul { margin: 0; padding-left: 18px; }
        @media (max-width: 700px) {
            .auth-card { padding: 24px; }
            .grid { grid-template-columns: 1fr; }
            .title { font-size: 26px; }
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <h1 class="title">Create Account</h1>
        <p class="sub">Get started with your customer account.</p>

        @if($errors->any())
            <div class="alert">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('signup.submit') }}">
            @csrf
            <input type="hidden" name="role" value="customer">

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
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
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

        <p class="helper">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
</body>
</html>
