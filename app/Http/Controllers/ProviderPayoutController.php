<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payout;
use Illuminate\Support\Str;

class ProviderPayoutController extends Controller
{
    public function requestPayout(Request $request)
    {
        $request->validate([
            'bank' => 'required',
            'account_number' => 'required',
            'account_holder' => 'required',
            'amount' => 'required|numeric|min:1'
        ]);

        $provider = auth()->user();
        $amount = (float) $request->amount;

        $commission = $amount * 0.10;
        $total = $amount + $commission;

        $payout = Payout::create([
            'payout_id' => Str::uuid(),
            'provider_id' => $provider->user_id,
            'amount' => $total, 
            'currency' => 'ZAR',
            'status' => 'SCHEDULED',
            'scheduled_at' => now(),
            'reference' => 'PAYOUT-'.rand(10000,99999)
        ]);

        return response()->json([
            'success' => true,
            'payout' => $payout
        ]);
    }
}