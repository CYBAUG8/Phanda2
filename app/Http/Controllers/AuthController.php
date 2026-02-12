<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // REGISTER (testing only)
    public function register(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|min:6',
            'full_name' => 'required|string'
        ]);

        $user = User::create([
            'user_id' => Str::uuid(),
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'full_name' => $data['full_name'],
            'role' => 'CUSTOMER',
            'status' => 'ACTIVE',
            'is_verified' => false,
        ]);

        Auth::login($user);

        return response()->json(['message' => 'User created'], 201);
    }

    
 public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($credentials)) {
        return back()->with('error', 'Invalid credentials');
    }

    $request->session()->regenerate();

     $user = Auth::user();

    LoginHistory::create([
        'login_history_id' => Str::uuid(),   
        'user_id' => $user->user_id,         
        'login_at' => now(),
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'device' => $this->parseDevice($request->userAgent()),
        'location' => 'Unknown',
        'status' => 'success',
    ]);

    return redirect()->intended('/users/settings');
}

private function parseDevice($ua)
{
    if (!$ua) return 'Unknown device';

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

        return response()->json(['message' => 'Logged out']);
    }
}