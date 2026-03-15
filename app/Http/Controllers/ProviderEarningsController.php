<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\Payout;
use App\Models\ProviderProfile;

class ProviderEarningsController extends Controller
{
    private const COMMISSION_RATE = 0.10;

    public function index(Request $request)
    {
        $user = $request->user();

        // Ensure only providers can access
        abort_if(!$user || $user->role !== 'provider', 403);

        // Get provider profile
        $providerProfile = ProviderProfile::where('user_id', $user->user_id)->firstOrFail();

        $providerId = $providerProfile->provider_id;

        /*
        |--------------------------------------------------------------------------
        | Get Provider Bookings
        |--------------------------------------------------------------------------
        */

        $bookingQuery = ServiceRequest::whereHas('service', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        });

        /*
        |--------------------------------------------------------------------------
        | Total Revenue (completed bookings only)
        |--------------------------------------------------------------------------
        */

        $totalRevenue = ServiceRequest::whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id',$providerProfile->provider_id);
        })
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

        /*
        |--------------------------------------------------------------------------
        | Processing Requests
        |--------------------------------------------------------------------------
        */
        
        $processingRequests = Payout::where('provider_id', $user->user_id)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($payout) {
            return [
                'id' => $payout->payout_id,
                'amount' => (float) $payout->amount, // convert to number
                'status' => $payout->status,
            ];
        });

        return view('providers.earnings', [
            'availableBalance' => $availableBalance,
            'totalRevenue' => $totalRevenue,
            'commission' => $commission,
            'processingRequests' => $processingRequests,
        ]);
    }
    public function refreshPayouts(Request $request)
    {
        $user = $request->user();
        $payouts = Payout::where('provider_id', $user->user_id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json(['payouts' => $payouts]);
    }
}