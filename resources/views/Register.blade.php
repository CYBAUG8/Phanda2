<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phanda · create account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .bg-light-pattern {
            background-color: #ffffff;
            background-image: radial-gradient(rgba(249, 115, 22, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        /* Role toggle pill */
        .role-pill input[type="radio"] { display: none; }
        .role-pill input[type="radio"]:checked + label {
            background-color: #ea580c;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(234,88,12,0.30);
        }
        .role-pill label {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        /* Password strength bar */
        #strength-bar {
            transition: width 0.35s ease, background-color 0.35s ease;
        }
    </style>
</head>
<body class="bg-light-pattern font-sans antialiased flex items-center justify-center min-h-screen p-4 py-10">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <a href="/" class="block text-center mb-6 group">
            <span class="text-3xl font-bold text-orange-600 group-hover:text-orange-700 transition">Phanda</span>
            <span class="block text-xs text-black/40 tracking-wider mt-1">where users meet providers</span>
        </a>

        <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl shadow-orange-900/5 border border-black/10 p-8 md:p-10">
            <h1 class="text-2xl font-bold text-gray-800 mb-2 text-center">Create your account</h1>
            <p class="text-sm text-black/40 text-center mb-8">Join Phanda as a customer or provider</p>

            {{-- Validation errors --}}
            @if($errors->any())
                <div class="mb-6 bg-orange-50 border-l-4 border-orange-500 text-orange-700 px-4 py-3 rounded-r-lg text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <i class="fas fa-exclamation-circle shrink-0"></i>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-orange-50 border-l-4 border-orange-500 text-orange-700 px-4 py-3 rounded-r-lg text-sm flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}" class="space-y-5">
                @csrf

                {{-- ── Role selector ── --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-tag mr-1 text-orange-400"></i> I am a…
                    </label>
                    <div class="role-pill grid grid-cols-2 gap-2 bg-black/5 p-1 rounded-xl">
                        <div>
                            <input type="radio" name="role" id="role_customer" value="customer"
                                   {{ old('role', 'customer') === 'customer' ? 'checked' : '' }}>
                            <label for="role_customer"
                                   class="flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold text-black/50">
                                <i class="fas fa-user-astronaut text-orange-400"></i> Customer
                            </label>
                        </div>
                        <div>
                            <input type="radio" name="role" id="role_provider" value="provider"
                                   {{ old('role') === 'provider' ? 'checked' : '' }}>
                            <label for="role_provider"
                                   class="flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold text-black/50">
                                <i class="fas fa-rocket text-orange-400"></i> Provider
                            </label>
                        </div>
                    </div>
                </div>

                {{-- ── Full name ── --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <i class="fas fa-id-card mr-1 text-orange-400"></i> Full name
                    </label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                           class="w-full border border-black/20 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-300 transition bg-white/70"
                           placeholder="Jane Doe">
                </div>

                {{-- ── Email ── --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <i class="far fa-envelope mr-1 text-orange-400"></i> Email
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full border border-black/20 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-300 transition bg-white/70"
                           placeholder="name@example.com">
                </div>

                {{-- ── Phone ── --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <i class="fas fa-phone mr-1 text-orange-400"></i> Phone number
                    </label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required
                           class="w-full border border-black/20 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-300 transition bg-white/70"
                           placeholder="+27 71 000 0000">
                </div>

                {{-- ── Password ── --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <i class="fas fa-lock mr-1 text-orange-400"></i> Password
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full border border-black/20 rounded-xl px-4 py-3 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-300 transition bg-white/70"
                               placeholder="min. 6 characters">
                        <button type="button" id="toggle-pw"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-black/30 hover:text-orange-500 transition text-sm"
                                tabindex="-1">
                            <i class="fas fa-eye" id="pw-icon"></i>
                        </button>
                    </div>
                    {{-- Strength indicator --}}
                    <div class="mt-2 h-1 w-full bg-black/10 rounded-full overflow-hidden">
                        <div id="strength-bar" class="h-full rounded-full w-0 bg-orange-300"></div>
                    </div>
                    <p id="strength-label" class="text-xs text-black/30 mt-1"></p>
                </div>

                {{-- ── Confirm password ── --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <i class="fas fa-lock mr-1 text-orange-400"></i> Confirm password
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="w-full border border-black/20 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-300 transition bg-white/70"
                           placeholder="••••••••">
                    <p id="pw-match" class="text-xs mt-1 hidden"></p>
                </div>

                {{-- ── Terms ── --}}
                <label class="flex items-start gap-3 text-sm text-black/50 cursor-pointer">
                    <input type="checkbox" name="terms" required
                           class="mt-0.5 rounded border-black/30 text-orange-600 focus:ring-orange-500 shrink-0">
                    <span>I agree to Phanda's
                        <a href="#" class="text-orange-600 hover:underline font-medium">Terms of Service</a>
                        and
                        <a href="#" class="text-orange-600 hover:underline font-medium">Privacy Policy</a>.
                    </span>
                </label>

                <button type="submit"
                        class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3.5 rounded-xl transition duration-200 shadow-md shadow-orange-600/30 flex items-center justify-center gap-2 text-base">
                    <i class="fas fa-user-plus"></i> Create account
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-black/40 border-t border-black/10 pt-6">
                <span>Already have an account?</span>
                <a href="/login" class="ml-1 text-orange-600 hover:text-orange-800 font-semibold hover:underline transition">
                    Log in <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>

        <p class="text-center text-xs text-black/40 mt-6">© 2025 Phanda – secure, simple connection</p>
    </div>

    <script>
        // Toggle password visibility
        const pwInput = document.getElementById('password');
        const pwIcon  = document.getElementById('pw-icon');
        document.getElementById('toggle-pw').addEventListener('click', () => {
            const show = pwInput.type === 'password';
            pwInput.type = show ? 'text' : 'password';
            pwIcon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        });

        // Password strength meter
        const bar   = document.getElementById('strength-bar');
        const label = document.getElementById('strength-label');
        pwInput.addEventListener('input', () => {
            const v = pwInput.value;
            let score = 0;
            if (v.length >= 6)  score++;
            if (v.length >= 10) score++;
            if (/[A-Z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;

            const levels = [
                { w: '0%',   color: '',                text: '' },
                { w: '25%',  color: 'bg-red-400',      text: 'Weak' },
                { w: '50%',  color: 'bg-yellow-400',   text: 'Fair' },
                { w: '75%',  color: 'bg-orange-400',   text: 'Good' },
                { w: '90%',  color: 'bg-green-400',    text: 'Strong' },
                { w: '100%', color: 'bg-green-500',    text: 'Very strong' },
            ];
            const l = levels[Math.min(score, 5)];
            bar.style.width = l.w;
            bar.className = `h-full rounded-full transition-all duration-300 ${l.color}`;
            label.textContent = l.text;
            label.className = `text-xs mt-1 ${score <= 1 ? 'text-red-400' : score <= 2 ? 'text-yellow-500' : 'text-green-500'}`;
        });

        // Password match indicator
        const confirmInput = document.getElementById('password_confirmation');
        const matchLabel   = document.getElementById('pw-match');
        function checkMatch() {
            if (!confirmInput.value) { matchLabel.classList.add('hidden'); return; }
            matchLabel.classList.remove('hidden');
            if (pwInput.value === confirmInput.value) {
                matchLabel.textContent = '✓ Passwords match';
                matchLabel.className = 'text-xs mt-1 text-green-500';
            } else {
                matchLabel.textContent = '✗ Passwords do not match';
                matchLabel.className = 'text-xs mt-1 text-red-400';
            }
        }
        confirmInput.addEventListener('input', checkMatch);
        pwInput.addEventListener('input', checkMatch);
    </script>
</body>
</html>