<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payout;
use Illuminate\Support\Facades\Auth;

class ProviderDashboardController extends Controller
{
    public function index()
    {
        if (app()->environment('local') && !auth()->check()) {
            $user = User::first();

            if ($user) {
                auth()->login($user);
            }
        }

        $user = auth()->user();

        if (!$user) {
            abort(403, 'Not authenticated');
        }
        // Total Bookings
        $totalBookings = Booking::where('user_id', $provider->user_id)->count();

        // Completed Bookings
        $completedBookings = Booking::where('user_id', $provider->user_id)
            ->where('status', 'completed')
            ->count();

        // Pending Bookings
        $pendingBookings = Booking::where('user_id', $provider->user_id)
            ->where('status', 'pending')
            ->count();

        // Total Revenue (Only completed bookings)
        $totalRevenue = Booking::where('user_id', $provider->user_id)
            ->where('status', 'completed')
            ->sum('total_price');

        // Commission (10%)
        $commissionRate = 0.10;
        $commission = $totalRevenue * $commissionRate;

        // Net Earnings
        $netEarnings = $totalRevenue - $commission;

        // Total Paid Out
        $totalPaidOut = Payout::where('provider_id', $provider->user_id)
            ->where('status', 'paid')
            ->sum('amount');

        // Available Balance
        $availableBalance = $netEarnings - $totalPaidOut;

        // Recent Bookings
        $recentBookings = Booking::where('user_id', $provider->user_id)
            ->latest()
            ->take(5)
            ->get();

        return view('providers.dashboard', compact(
            'totalBookings',
            'completedBookings',
            'pendingBookings',
            'totalRevenue',
            'commission',
            'netEarnings',
            'totalPaidOut',
            'availableBalance',
            'recentBookings'
        ));
    }
}