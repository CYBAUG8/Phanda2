<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payout;
use App\Models\Review;
use App\Models\ProviderProfile;
use Illuminate\Support\Facades\Auth;

class ProviderDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Not authenticated');
        }

        $providerId = $user->user_id;
        $providerProfile = ProviderProfile::where('user_id', $user->user_id)
            ->with('services')
            ->firstOrFail();

        // -----------------------------
        // BOOKINGS
        // -----------------------------

        $totalBookings = Booking::whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id', $providerProfile->provider_id);
            })->count();

        $completedBookings = Booking::whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id',$providerProfile->provider_id);
        })
        ->where('status', 'completed')
        ->count();

        $pendingBookings = Booking::whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id',$providerProfile->provider_id);
        })
        ->where('status', 'in_progress')
        ->count();

        // -----------------------------
        // REVENUE
        // -----------------------------

        $totalRevenue = Booking::whereHas('service', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })
        ->where('status', 'completed')
        ->sum('total_price');

        $commissionRate = 0.10;
        $commission = $totalRevenue * $commissionRate;

        $netEarnings = $totalRevenue - $commission;

        // -----------------------------
        // PAYOUTS
        // -----------------------------

        $totalPaidOut = Payout::where('provider_id', $providerId)
            ->where('status', 'paid')
            ->sum('amount');

        $availableBalance = $netEarnings - $totalPaidOut;

        // -----------------------------
        // REVIEWS
        // -----------------------------

        $averageRating = Review::where('to_user_id', $providerId)
            ->avg('rating');

        $totalReviews = Review::where('to_user_id', $providerId)
            ->count();

        // -----------------------------
        // RECENT BOOKINGS
        // -----------------------------

        $recentBookings = Booking::whereHas('service', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })
        ->latest()
        ->take(5)
        ->get();
        
        // -----------------------------
        // Is Online
        // -----------------------------
        $profile = ProviderProfile::where('user_id', $providerId)->first();

        $isOnline = $profile ? $profile->is_online : false;

        return view('providers.dashboard', compact(
            'totalBookings',
            'completedBookings',
            'pendingBookings',
            'totalRevenue',
            'commission',
            'netEarnings',
            'totalPaidOut',
            'availableBalance',
            'recentBookings',
            'averageRating',
            'totalReviews',
            'isOnline'
        ));
    }

    public function toggleOnline(Request $request)
    {
        $user = Auth::user();

        $profile = ProviderProfile::where('user_id', $user->user_id)->first();

        if (!$profile) {
            return response()->json(['success' => false]);
        }

        $profile->is_online = $request->is_online;
        $profile->save();

        return response()->json([
            'success' => true,
            'is_online' => $profile->is_online
        ]);
    }
}