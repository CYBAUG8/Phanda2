<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Provider Login — Phanda</title>
    {{-- Minimal inline styles so the form is usable even before Vite builds --}}
    <style>
        body{ font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, Arial; background:#fafafa; color:#0b0b0b; display:flex; align-items:center; justify-content:center; height:100vh; margin:0 }
        .login-card{ background:#fff; padding:22px; border-radius:12px; box-shadow:0 12px 32px rgba(11,11,11,0.06); width:360px }
        .login-card h2{ margin:0 0 8px }
        .field{ margin-top:12px }
        input{ width:100%; padding:10px 12px; border:1px solid #e6e6e6; border-radius:8px }
        .btn{ margin-top:16px; background:#ff6a00; color:#fff; border:none; padding:10px 12px; border-radius:8px; width:100%; font-weight:700 }
        .muted{ color:rgba(11,11,11,0.6); font-size:14px }
        .error{ color:#d23; margin-top:8px }
        a.back{ display:inline-block; margin-top:12px; color:#333; font-size:13px }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Provider Sign in</h2>
        <p class="muted">Sign in with your provider account to access the dashboard.</p>

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <!-- Simplified: use GET submit so CSRF is not required for demo/no-security login -->
        <form method="GET" action="/provider/enter">
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required placeholder="provider@example.com" />
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required placeholder="secret" />
            </div>
            <button class="btn" type="submit">Sign in</button>
        </form>

        <a class="back" href="/">← Back to start</a>
    </div>
</body>
</html>