<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payout;
use App\Models\ProviderProfile;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_if(!$user, 403, 'Not authenticated');

        $profile = ProviderProfile::where('user_id', $user->user_id)->first();
        abort_if(!$profile, 403, 'Provider profile not found');

<<<<<<< HEAD
        $providerId = $user->user_id;
        $providerProfile = ProviderProfile::where('user_id', $user->user_id)
            ->with('services')
            ->firstOrFail();
=======
        $providerId = $profile->provider_id;
>>>>>>> services-bookings-feature

        $bookingQuery = Booking::whereHas('service', function ($q) use ($providerId) {
            $q->where('provider_id', $providerId);
        });

<<<<<<< HEAD
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
        ->where('status', 'pending')
        ->count();

        // -----------------------------
        // REVENUE
        // -----------------------------

        $totalRevenue = Booking::whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id',$providerProfile->provider_id);
        })
        ->where('status', 'completed')
        ->sum('total_price');

        $commissionRate = 0.10;
        $commission = $totalRevenue * $commissionRate;

        $netEarnings = $totalRevenue ;
=======
        $totalBookings = (clone $bookingQuery)->count();
        $completedBookings = (clone $bookingQuery)->where('status', 'completed')->count();
        $pendingBookings = (clone $bookingQuery)->where('status', 'pending')->count();

        $totalRevenue = (clone $bookingQuery)->where('status', 'completed')->sum('total_price');

        $commissionRate = 0.10;
        $commission = $totalRevenue * $commissionRate;
        $netEarnings = $totalRevenue - $commission;
>>>>>>> services-bookings-feature

        $totalPaidOut = Payout::where('provider_id', $user->user_id)
            ->where('status', 'paid')
            ->sum('amount');

        $availableBalance = $netEarnings - $totalPaidOut;

        $averageRating = (float) (Review::where('to_user_id', $user->user_id)->avg('rating') ?? 0);
        $totalReviews = Review::where('to_user_id', $user->user_id)->count();

<<<<<<< HEAD
        $averageRating = Review::where('to_user_id', $providerId)
            ->avg('rating');

        $totalReviews = Review::where('to_user_id', $providerId)
            ->count();

        // -----------------------------
        // RECENT BOOKINGS
        // -----------------------------
        $recentBookings = Booking::with('service')
        ->whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id', $providerProfile->provider_id);
        })
        ->latest()
        ->take(10)
        ->get();
        
        // -----------------------------
        // Is Online
        // -----------------------------
        $profile = ProviderProfile::where('user_id', $providerId)->first();
=======
        $recentBookings = (clone $bookingQuery)
            ->with('service')
            ->latest()
            ->take(5)
            ->get();
>>>>>>> services-bookings-feature

        $isOnline = (bool) $profile->is_online;

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
            return response()->json(['success' => false], 404);
        }

        $profile->is_online = $request->boolean('is_online');
        $profile->save();

        return response()->json([
            'success' => true,
            'is_online' => (bool) $profile->is_online,
        ]);
    }
}
