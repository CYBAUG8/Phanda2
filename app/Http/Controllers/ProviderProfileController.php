<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProviderProfileController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        abort_if(!$user || $user->role !== 'provider', 403, 'Unauthorized access.');

        $providerProfile = ProviderProfile::where('user_id', $user->user_id)
            ->with('services')
            ->firstOrFail();

        $bookingQuery = Booking::whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id', $providerProfile->provider_id);
        });

        $data = [
            'provider_id' => $providerProfile->provider_id,
            'business_name' => $providerProfile->business_name,
            'bio' => $providerProfile->bio,
            'years_experience' => $providerProfile->years_experience,
            'service_area' => $providerProfile->service_area,
            'service_radius_km' => $providerProfile->service_radius_km,
            'last_lat' => $providerProfile->last_lat,
            'last_lng' => $providerProfile->last_lng,
            'kyc_status' => $providerProfile->kyc_status,
            'rating_avg' => $providerProfile->rating_avg,
            'phone' => $user->phone,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'services' => $providerProfile->services,
            'total_bookings' => (clone $bookingQuery)->count(),
            'active_jobs' => (clone $bookingQuery)->whereIn('status', ['confirmed', 'in_progress'])->count(),
            'completed_jobs' => (clone $bookingQuery)->where('status', 'completed')->count(),
            'total_earnings' => (float) (clone $bookingQuery)->where('status', 'completed')->sum('total_price'),
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
            'years_experience' => 'sometimes|nullable|integer|min:0',
            'bio' => 'sometimes|nullable|string',
            'service_radius_km' => 'sometimes|nullable|numeric|min:1|max:100',
            'last_lat' => 'sometimes|nullable|numeric|between:-90,90',
            'last_lng' => 'sometimes|nullable|numeric|between:-180,180',
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
            'years_experience',
            'bio',
            'service_radius_km',
            'last_lat',
            'last_lng',
        ]));
        $providerProfile->save();

        return response()->json([
            'provider_id' => $providerProfile->provider_id,
            'business_name' => $providerProfile->business_name,
            'bio' => $providerProfile->bio,
            'years_experience' => $providerProfile->years_experience,
            'service_area' => $providerProfile->service_area,
            'service_radius_km' => $providerProfile->service_radius_km,
            'last_lat' => $providerProfile->last_lat,
            'last_lng' => $providerProfile->last_lng,
            'phone' => $user->phone,
            'email' => $user->email,
        ]);
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
