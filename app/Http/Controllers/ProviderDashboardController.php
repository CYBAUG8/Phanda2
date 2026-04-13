<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
=======
use App\Models\Booking;
>>>>>>> feature2
use App\Models\Payout;
use App\Models\ProviderProfile;
use App\Models\Review;
use App\Services\BookingLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderDashboardController extends Controller
{
    public function index(BookingLifecycleService $bookingLifecycleService)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $profile = ProviderProfile::where('user_id', $user->user_id)->first();

        if (!$profile) {
            return view('Providers.dashboard', [
                'totalBookings' => 0,
                'completedBookings' => 0,
                'pendingBookings' => 0,
                'totalRevenue' => 0,
                'commission' => 0,
                'netEarnings' => 0,
                'totalPaidOut' => 0,
                'availableBalance' => 0,
                'recentBookings' => collect(),
                'averageRating' => 0,
                'totalReviews' => 0,
                'isOnline' => false,
            ])->with('error', 'Provider profile not found. Complete your profile to enable dashboard data.');
        }

<<<<<<< HEAD
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
=======
        $providerId = $profile->provider_id;

        $bookingQuery = Booking::whereHas('service', function ($q) use ($providerId) {
            $q->where('provider_id', $providerId);
        });

        $bookingLifecycleService->expireStaleBookings($bookingQuery);
>>>>>>> feature2

        $totalBookings = (clone $bookingQuery)->count();
        $completedBookings = (clone $bookingQuery)->where('status', 'completed')->count();
        $pendingBookings = (clone $bookingQuery)->where('status', 'pending')->count();

<<<<<<< HEAD
        $totalRevenue = ServiceRequest::whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id',$providerProfile->provider_id);
        })
        ->where('status', 'completed')
        ->sum('total_price');
=======
        $totalRevenue = (clone $bookingQuery)->where('status', 'completed')->sum('total_price');
>>>>>>> feature2

        $commissionRate = 0.10;
        $commission = $totalRevenue * $commissionRate;
        $netEarnings = $totalRevenue - $commission;

<<<<<<< HEAD
       /*
        |--------------------------------------------------------------------------
        | Withdrawals
        |--------------------------------------------------------------------------
        */

        $totalWithdrawn = (float) Payout::where('provider_id', $user->user_id)
            ->whereIn('status', ['PAID', 'paid', 'SCHEDULED', 'scheduled'])
=======
        $totalPaidOut = Payout::where('provider_id', $user->user_id)
            ->whereIn('status', ['PAID', 'paid'])
>>>>>>> feature2
            ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | Available Balance
        |--------------------------------------------------------------------------
        */
        $availableBalance = ($totalRevenue - $totalWithdrawn);

        $averageRating = (float) (Review::where('to_user_id', $user->user_id)->avg('rating') ?? 0);
        $totalReviews = Review::where('to_user_id', $user->user_id)->count();

        $recentBookings = (clone $bookingQuery)
            ->with('service')
            ->latest()
            ->take(5)
            ->get();

        $isOnline = (bool) $profile->is_online;

<<<<<<< HEAD
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
=======
        return view('Providers.dashboard', compact(
>>>>>>> feature2
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
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $profile = ProviderProfile::where('user_id', $user->user_id)->first();

        if (!$profile) {
            return response()->json(['success' => false, 'message' => 'Provider profile not found'], 404);
        }

        $profile->is_online = $request->boolean('is_online');
        $profile->save();

        return response()->json([
            'success' => true,
            'is_online' => (bool) $profile->is_online,
        ]);
    }
}



