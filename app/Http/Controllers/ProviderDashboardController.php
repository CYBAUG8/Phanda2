<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
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

        $totalBookings = ServiceRequest::whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id', $providerProfile->provider_id);
            })->count();

        $completedBookings = ServiceRequest::whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id',$providerProfile->provider_id);
        })
        ->where('status', 'completed')
        ->count();

        $pendingBookings = ServiceRequest::whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id',$providerProfile->provider_id);
        })
        ->where('status', 'pending')
        ->count();

        // -----------------------------
        // REVENUE
        // -----------------------------

        $totalRevenue = ServiceRequest::whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id',$providerProfile->provider_id);
        })
        ->where('status', 'completed')
        ->sum('total_price');

        $commissionRate = 0.10;
        $commission = $totalRevenue * $commissionRate;

       /*
        |--------------------------------------------------------------------------
        | Withdrawals
        |--------------------------------------------------------------------------
        */

        $totalWithdrawn = (float) Payout::where('provider_id', $user->user_id)
            ->whereIn('status', ['PAID', 'paid', 'SCHEDULED', 'scheduled'])
            ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | Available Balance
        |--------------------------------------------------------------------------
        */
        $availableBalance = ($totalRevenue - $totalWithdrawn);

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
        $recentBookings = ServiceRequest::with('service')
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

        $isOnline = $profile ? $profile->is_online : false;

        return view('providers.dashboard', compact(
            'totalBookings',
            'completedBookings',
            'pendingBookings',
            'totalRevenue',
            'commission',
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