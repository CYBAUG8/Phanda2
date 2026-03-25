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
        abort_if(!$user || $user->role !== 'provider', 403);

        $providerProfile = ProviderProfile::where('user_id', $user->user_id)->firstOrFail();

        // Total Revenue
        $totalRevenue = ServiceRequest::whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id', $providerProfile->provider_id);
        })
        ->where('status', 'completed')
        ->sum('total_price');

        // 🔥 TOTAL WITHDRAWN (already includes commission)
        $totalWithdrawn = Payout::where('provider_id', $user->user_id)
            ->whereIn('status', ['PAID', 'SCHEDULED'])
            ->sum('amount');

        $availableBalance = $totalRevenue - $totalWithdrawn;

        $processingRequests = Payout::where('provider_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($payout) {
                return [
                    'id' => $payout->payout_id,
                    'amount' => (float) $payout->amount,
                    'status' => strtoupper($payout->status),
                ];
            });

        return view('providers.earnings', [
            'availableBalance' => $availableBalance,
            'totalRevenue' => $totalRevenue,
            'processingRequests' => $processingRequests,
        ]);
    }

    // 🔥 CREATE PAYOUT (CRITICAL FIX HERE)
    public function requestPayout(Request $request)
    {
        $user = $request->user();

        $amount = (float) $request->amount;

        $commission = $amount * self::COMMISSION_RATE;
        $total = $amount + $commission;

        $payout = Payout::create([
            'provider_id' => $user->user_id,
            'amount' => $total, // 🔥 STORE TOTAL (amount + commission)
            'status' => 'SCHEDULED',
        ]);

        return response()->json([
            'payout' => $payout
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