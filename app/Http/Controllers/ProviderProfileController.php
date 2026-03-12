<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProviderProfileController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $providerProfile = ProviderProfile::where('user_id', $user->user_id)
            ->with('services')
            ->first();

        if (!$providerProfile) {
            return redirect()->route('users.dashboard')
                ->with('error', 'Provider profile not found for this account.');
        }

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

        return view('Providers.profile', ['provider' => $data]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'service_area' => 'nullable|string|max:255',
            'service_radius_km' => 'nullable|numeric|min:1|max:100',
            'last_lat' => 'nullable|numeric|between:-90,90',
            'last_lng' => 'nullable|numeric|between:-180,180',
            'years_experience' => 'nullable|integer|min:0',
            'bio' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $profile = ProviderProfile::withTrashed()->where('user_id', $user->user_id)->first();

        if ($profile) {
            if ($profile->trashed()) {
                $profile->restore();
            }

            $profile->fill($data);
            $profile->save();

            return response()->json($profile, 200);
        }

        $profile = ProviderProfile::create([
            'provider_id' => (string) Str::uuid(),
            'user_id' => $user->user_id,
            'business_name' => $data['business_name'],
            'service_area' => $data['service_area'] ?? null,
            'service_radius_km' => $data['service_radius_km'] ?? null,
            'last_lat' => $data['last_lat'] ?? null,
            'last_lng' => $data['last_lng'] ?? null,
            'years_experience' => $data['years_experience'] ?? 0,
            'bio' => $data['bio'] ?? null,
            'kyc_status' => 'PENDING',
            'is_online' => false,
            'rating_avg' => 0,
        ]);

        return response()->json($profile, 201);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
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
            'last_lat' => 'sometimes|nullable|numeric|between:-90,90',
            'last_lng' => 'sometimes|nullable|numeric|between:-180,180',
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
            'bio',
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
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $providerProfile = ProviderProfile::withTrashed()->where('user_id', $user->user_id)->first();

        if (!$providerProfile) {
            return response()->json(['message' => 'Provider profile not found'], 404);
        }

        DB::transaction(function () use ($providerProfile) {
            Service::where('provider_id', $providerProfile->provider_id)
                ->update(['is_active' => false]);

            Service::where('provider_id', $providerProfile->provider_id)->delete();

            if (!$providerProfile->trashed()) {
                $providerProfile->delete();
            }
        });

        return response()->json(['message' => 'Provider profile archived successfully']);
    }
}
