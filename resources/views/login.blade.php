<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
