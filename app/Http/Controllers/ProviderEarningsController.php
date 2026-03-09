<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payout;
use App\Models\ProviderProfile;

class ProviderEarningsController extends Controller
{
    private const COMMISSION_RATE = 0.10;

    public function index(Request $request)
    {
        $user = $request->user();

        // Ensure only providers access
        abort_if(!$user || $user->role !== 'provider', 403);

        // Get provider profile
        $providerProfile = ProviderProfile::where('user_id', $user->user_id)->firstOrFail();

        $providerId = $providerProfile->provider_id;

        /*
        |--------------------------------------------------------------------------
        | Get Provider Bookings
        |--------------------------------------------------------------------------
        */

        $bookingQuery = Booking::whereHas('service', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        });

        /*
        |--------------------------------------------------------------------------
        | Total Revenue (completed bookings only)
        |--------------------------------------------------------------------------
        */

        $totalRevenue = (float) (clone $bookingQuery)
            ->where('status', 'completed')
            ->sum('total_price');

        /*
        |--------------------------------------------------------------------------
        | Platform Commission
        |--------------------------------------------------------------------------
        */

        $commission = round($totalRevenue * self::COMMISSION_RATE, 2);

        /*
        |--------------------------------------------------------------------------
        | Net Earnings
        |--------------------------------------------------------------------------
        */

        $netEarnings = round($totalRevenue - $commission, 2);

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

        $availableBalance = max(0, $netEarnings - $totalWithdrawn);

        /*
        |--------------------------------------------------------------------------
        | Processing Requests
        |--------------------------------------------------------------------------
        */

        $processingRequests = Payout::where('provider_id', $user->user_id)
            ->whereIn('status', ['SCHEDULED', 'scheduled'])
            ->get();

        return view('providers.earnings', [
            'availableBalance' => $availableBalance,
            'totalRevenue' => $totalRevenue,
            'commission' => $commission,
            'netEarnings' => $netEarnings,
            'processingRequests' => $processingRequests,
        ]);
    }
}