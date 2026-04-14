<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\ProviderProfile;

class AuthController extends Controller
{
    

 
    // ─────────────────────────────────────────────
    //  Handle registration
    // ─────────────────────────────────────────────
    public function register(Request $request)
    {
        $data = $request->validate([
            'email'     => 'required|email|unique:users_profile,email',
            'phone'     => 'required|unique:users_profile,phone',
            'password'  => 'required|min:6|confirmed',   // expects password_confirmation field
            'full_name' => 'required|string|max:160',
            'role'      => 'required|in:customer,provider',
        ]);
 
        // ── Create user record ──────────────────────────────────────────
        $user = User::create([
            'user_id'   => (string) Str::uuid(),
            'email'     => $data['email'],
            'phone'     => $data['phone'],
            'password'  => Hash::make($data['password']),
            'full_name' => $data['full_name'],
            'role'      => strtoupper($data['role']),   // stored as CUSTOMER / PROVIDER
            'account_status' => 'ACTIVE',
            'is_verified'    => false,
        ]);
 
        // ── Customer profile ────────────────────────────────────────────
        if (strtoupper($data['role']) === 'CUSTOMER') {
            UserProfile::create([
                'user_id'   => $user->user_id,
                'full_name' => $user->full_name,
                'email'     => $user->email,
                'phone'     => $user->phone,
                'role'      => 'CUSTOMER',
                'account_status' => 'ACTIVE',
                // gender / member_id left null until the user fills their profile
            ]);
        }
 
        // ── Provider profile ────────────────────────────────────────────
        if (strtoupper($data['role']) === 'PROVIDER') {
            ProviderProfile::create([
                'provider_id'    => (string) Str::uuid(),
                'user_id'        => $user->user_id,
                'business_name'  => $user->full_name . "'s Business",
                'bio'            => null,
                'years_experience' => 0,
            ]);
        }
 
        // ── Log the user in ─────────────────────────────────────────────
        Auth::login($user);
 
       
        return redirect('/dashboard')->with('success', 'Welcome to Phanda, ' . $user->full_name . '!');
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
     $this->ensureProviderProfile($user);

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

        if (strtoupper($user->role) === 'PROVIDER') {

            return redirect()->route('providers.dashboard');
            
        }else{

            return redirect()->intended(route('users.dashboard'));
        }
   
    

}

private function ensureProviderProfile(User $user): void
{
    if (strtolower((string) $user->role) !== 'provider') {
        return;
    }

    $profile = ProviderProfile::withTrashed()->firstOrNew([
        'user_id' => $user->user_id,
    ]);

    if (!$profile->provider_id) {
        $profile->provider_id = (string) Str::uuid();
    }

    if (!$profile->business_name) {
        $profile->business_name = $user->full_name ?: "Provider {$user->user_id}";
    }

    if ($profile->years_experience === null) {
        $profile->years_experience = 0;
    }

    if (!$profile->service_area) {
        $profile->service_area = 'Johannesburg';
    }

    if (!$profile->kyc_status) {
        $profile->kyc_status = 'PENDING';
    }

    if (!$profile->exists) {
        $profile->is_online = false;
        $profile->service_radius_km = 25;
        $profile->rating_avg = 0;
    }

    $profile->deleted_at = null;
    $profile->save();
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

        

        return redirect('/login');
    }
}
