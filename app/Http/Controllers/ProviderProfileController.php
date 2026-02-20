<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProviderProfileController extends Controller
{
    /**
     * Store a newly created provider profile.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'business_name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
        ]);

        // Check if profile already exists for this user
        if (ProviderProfile::where('user_id', $data['user_id'])->exists()) {
            return response()->json(['message' => 'Provider profile already exists'], 409);
        }

        $providerProfile = ProviderProfile::create([
            'provider_id' => (string) \Illuminate\Support\Str::uuid(), // generate UUID
            'user_id' => $data['user_id'],
            'business_name' => $data['business_name'],
            'bio' => $data['bio'] ?? null,
            'years_experience' => $data['years_experience'] ?? 0,
        ]);

        return response()->json($providerProfile, 201);
    }

    /**
     * Display the specified provider profile.
     */
    public function profile()
{
    $user = Auth::user();
    
    // Ensure user is logged in and is a provider
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
        'total_bookings' => 0,
        'active_jobs' => 0,
        'completed_jobs' => 0,
        'total_earnings' => 0,
    ];

    return view('providers.profile', ['provider' => $data]);
}

    /**
     * Update the authenticated provider's profile.
     */
    public function update(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Ensure the user is a provider and has a profile
        if ($user->role !== 'provider') {
            return response()->json(['message' => 'User is not a provider'], 403);
        }

        $providerProfile = ProviderProfile::where('user_id', $user->user_id)->first();

        if (!$providerProfile) {
            return response()->json(['message' => 'Provider profile not found'], 404);
        }

        // Validate input: include fields from both users and provider_profiles
        $validator = Validator::make($request->all(), [
            'business_name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20', // phone belongs to users table
            'service_area' => 'sometimes|nullable|string|max:255',
            'years_experience' => 'sometimes|nullable|integer|min:0',
            'bio' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update user's phone if provided
        if ($request->has('phone')) {
            $user->phone = $request->phone;
            $user->save();
        }

        // Update provider profile fields
        $providerProfile->fill($request->only([
            'business_name',
            'service_area',
            'years_experience',
            'bio'
        ]));

        $providerProfile->save();

        // Return updated data
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

    /**
     * Remove the authenticated provider's profile.
     */
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