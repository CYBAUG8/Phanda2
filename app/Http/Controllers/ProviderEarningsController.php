<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payout;
use App\Models\ProviderProfile;
=======
use App\Models\Booking;
use App\Models\Payout;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
>>>>>>> services-bookings-feature

class ProviderEarningsController extends Controller
{
    private const COMMISSION_RATE = 0.10;

    public function index(Request $request)
    {
        $user = $request->user();
<<<<<<< HEAD

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

=======
        abort_if(!$user || $user->role !== 'provider', 403, 'Unauthorized access.');

        $providerProfile = ProviderProfile::where('user_id', $user->user_id)->firstOrFail();

        $summary = $this->buildSummary($providerProfile->provider_id, $user->user_id);

        $processingRequests = Payout::query()
            ->where('provider_id', $user->user_id)
            ->whereIn('status', ['SCHEDULED', 'scheduled'])
            ->orderByDesc('created_at')
            ->get(['payout_id', 'amount', 'status', 'created_at']);

        return view('Providers.earnings', [
            'availableBalance' => $summary['availableBalance'],
            'totalRevenue' => $summary['totalRevenue'],
            'commission' => $summary['commission'],
            'netEarnings' => $summary['netEarnings'],
            'processingRequests' => $processingRequests,
            'withdrawalSuccess' => (bool) $request->session()->get('withdrawal_success', false),
        ]);
    }

    public function withdraw(Request $request)
    {
        $user = $request->user();
        abort_if(!$user || $user->role !== 'provider', 403, 'Unauthorized access.');

        $providerProfile = ProviderProfile::where('user_id', $user->user_id)->firstOrFail();

        $validated = $request->validate([
            'bank' => 'required|string|in:FNB,Standard Bank,ABSA,Nedbank,Capitec',
            'account_number' => 'required|string|regex:/^\d{6,20}$/',
            'account_holder' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1|max:99999999.99',
        ]);

        $summary = $this->buildSummary($providerProfile->provider_id, $user->user_id);
        $amount = round((float) $validated['amount'], 2);

        if ($amount > $summary['availableBalance']) {
            return back()
                ->withErrors(['amount' => 'Amount cannot exceed available balance.'])
                ->withInput();
        }

        $accountDigits = preg_replace('/\D+/', '', (string) $validated['account_number']);
        $last4 = substr((string) $accountDigits, -4);

        Payout::create([
            'provider_id' => $user->user_id,
            'amount' => $amount,
            'currency' => 'ZAR',
            'status' => 'SCHEDULED',
            'scheduled_at' => now()->addDay(),
            'reference' => sprintf(
                '%s-%s-%s',
                strtoupper(str_replace(' ', '', $validated['bank'])),
                $last4 ?: 'XXXX',
                strtoupper(Str::random(6))
            ),
        ]);

        return redirect()
            ->route('provider.earnings')
            ->with('success', 'Withdrawal request submitted successfully.')
            ->with('withdrawal_success', true);
    }

    private function buildSummary(string $providerId, string $userId): array
    {
>>>>>>> services-bookings-feature
        $bookingQuery = Booking::whereHas('service', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        });

<<<<<<< HEAD
        /*
        |--------------------------------------------------------------------------
        | Total Revenue (completed bookings only)
        |--------------------------------------------------------------------------
        */

=======
>>>>>>> services-bookings-feature
        $totalRevenue = (float) (clone $bookingQuery)
            ->where('status', 'completed')
            ->sum('total_price');

<<<<<<< HEAD
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

        $netEarnings = round($totalRevenue , 2);

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
=======
        $commission = round($totalRevenue * self::COMMISSION_RATE, 2);
        $netEarnings = max(0, round($totalRevenue - $commission, 2));

        $totalPaidOut = (float) Payout::query()
            ->where('provider_id', $userId)
            ->whereIn('status', ['PAID', 'paid'])
            ->sum('amount');

        $availableBalance = max(0, round($netEarnings - $totalPaidOut, 2));

        return [
            'totalRevenue' => $totalRevenue,
            'commission' => $commission,
            'netEarnings' => $netEarnings,
            'availableBalance' => $availableBalance,
        ];
    }
}
>>>>>>> services-bookings-feature
