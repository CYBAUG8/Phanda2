<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProviderProfileController extends Controller
{
    
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'business_name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
        ]);

       
        if (ProviderProfile::where('user_id', $data['user_id'])->exists()) {
            return response()->json(['message' => 'Provider profile already exists'], 409);
        }

        $providerProfile = ProviderProfile::create([
            'provider_id' => (string) \Illuminate\Support\Str::uuid(), 
            'user_id' => $data['user_id'],
            'business_name' => $data['business_name'],
            'bio' => $data['bio'] ?? null,
            'years_experience' => $data['years_experience'] ?? 0,
        ]);

        return response()->json($providerProfile, 201);
    }

    
public function profile()
{
    $user = Auth::user();
    
   
    if (!$user || $user->role !== 'provider') {
        abort(403, 'Unauthorized access.');
    }

    $providerProfile = ProviderProfile::where('user_id', $user->user_id)
                        ->with('user', 'services')
                        ->firstOrFail();

    $data = [
        'provider_id' => $providerProfile->provider_id,
        'business_name' => $providerProfile->business_name,
        'bio' => $providerProfile->bio,
        'years_experience' => $providerProfile->years_experience,
        'service_area' => $providerProfile->service_area,
        'kyc_status' => $providerProfile->kyc_status,
        'rating_avg' => $providerProfile->rating_avg,
        'phone' => $providerProfile->user->phone,
        'email' => $providerProfile->user->email,
        'full_name' => $providerProfile->user->full_name,
        'services' => $providerProfile->services,
        'total_bookings' => $providerProfile->bookings()->count(),
        'active_jobs' => $providerProfile->bookings()
             ->whereIn('status', ['confirmed', 'in_progress'])
             ->count(),
        'completed_jobs' => $providerProfile->bookings()
             ->where('status', 'completed')
             ->count(), 
        'total_earnings' => $providerProfile->bookings()
             ->where('status', 'completed')
             ->sum('total_price'),
    ];

    return view('providers.profile', ['provider' => $data]);
}

 
    public function update(Request $request)
    {
       
        $user = Auth::user();

       
        if ($user->role !== 'provider') {
            return response()->json(['message' => 'User is not a provider'], 403);
        }

        $providerProfile = ProviderProfile::where('user_id', $user->user_id)->first();

        if (!$providerProfile) {
            return response()->json(['message' => 'Provider profile not found'], 404);
        }


        $validator = Validator::make($request->all(), [
            'business_name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'service_area' => 'sometimes|nullable|string|max:255',
            'service_radius_km' => 'sometimes|nullable|numeric|min:1|max:100',
            'last_lat' => 'sometimes|nullable|numeric',
            'last_lng' => 'sometimes|nullable|numeric',
            'years_experience' => 'sometimes|nullable|integer|min:0',
            'bio' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

       
        if ($request->has('phone')) {
            $user->phone = $request->phone;
            $user->save();
        }

    
        $providerProfile->fill($request->only([
            'business_name',
            'service_area',
            'service_radius_km',
            'last_lat',
            'last_lng',
            'years_experience',
            'bio'
        ]));

        $providerProfile->save();

       
        $response = [
            'provider_id' => $providerProfile->provider_id,
            'business_name' => $providerProfile->business_name,
            'bio' => $providerProfile->bio,
            'years_experience' => $providerProfile->years_experience,
            'service_area' => $providerProfile->service_area,
            'phone' => $user->phone,
            'email' => $user->email,
        ];

        return response()->json($response);
    }

 
    public function destroy()
    {
        $user = Auth::user();

        if ($user->role !== 'provider') {
            return response()->json(['message' => 'User is not a provider'], 403);
        }

        $providerProfile = ProviderProfile::where('user_id', $user->user_id)->first();

        if (!$providerProfile) {
            return response()->json(['message' => 'Provider profile not found'], 404);
        }

        
        $providerProfile->delete();

     

        return response()->json(['message' => 'Provider profile deleted successfully']);
    }
}