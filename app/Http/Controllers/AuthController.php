<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30|unique:users,phone',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:customer,provider',
        ]);

        $user = User::create([
            'user_id' => (string) Str::uuid(),
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        if ($data['role'] === 'provider') {
            ProviderProfile::create([
                'provider_id' => (string) Str::uuid(),
                'user_id' => $user->user_id,
                'business_name' => $user->full_name,
                'bio' => null,
                'years_experience' => 0,
            ]);
        }

        Auth::login($user);

        return $data['role'] === 'provider'
            ? redirect()->route('providers.dashboard')
            : redirect()->route('users.dashboard');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->with('error', 'Invalid credentials')->withInput();
        }

        $request->session()->regenerate();

        $user = Auth::user();

        LoginHistory::create([
            'login_history_id' => (string) Str::uuid(),
            'user_id' => $user->user_id,
            'login_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device' => $this->parseDevice((string) $request->userAgent()),
            'location' => 'Unknown',
            'status' => 'success',
        ]);

        if ($user->role === 'provider') {
            return redirect()->route('providers.dashboard');
        }

        if ($user->role === 'customer') {
            return redirect()->route('users.dashboard');
        }

        return redirect()->route('users.dashboard');
    }

    private function parseDevice(string $ua): string
    {
        if (str_contains($ua, 'Edg')) return 'Windows PC (Edge)';
        if (str_contains($ua, 'Chrome')) return 'Windows PC (Chrome)';
        if (str_contains($ua, 'Firefox')) return 'Windows PC (Firefox)';
        if (str_contains($ua, 'Safari') && str_contains($ua, 'Mac')) return 'Mac (Safari)';
        if (str_contains($ua, 'Mac')) return 'Mac';
        if (str_contains($ua, 'Android')) return 'Android phone';
        if (str_contains($ua, 'iPhone')) return 'iPhone';

        return 'Unknown device';
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
