<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payout;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProviderEarningsController extends Controller
{
    private const COMMISSION_RATE = 0.10;
    private const HOLD_HOURS = 48;

    public function index(Request $request)
    {
        $user = $request->user();
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
            'onHoldNetEarnings' => $summary['onHoldNetEarnings'],
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
        $eligiblePaymentsQuery = Booking::whereHas('service', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })
            ->where('status', Booking::STATUS_COMPLETED)
            ->where('payment_status', Booking::PAYMENT_STATUS_PAID);

        $totalRevenue = (float) (clone $eligiblePaymentsQuery)
            ->sum('total_price');

        $commission = round($totalRevenue * self::COMMISSION_RATE, 2);
        $netEarnings = max(0, round($totalRevenue - $commission, 2));

        $holdCutoff = now()->subHours(self::HOLD_HOURS);

        $availableRevenue = (float) (clone $eligiblePaymentsQuery)
            ->where('updated_at', '<=', $holdCutoff)
            ->sum('total_price');

        $availableCommission = round($availableRevenue * self::COMMISSION_RATE, 2);
        $availableNet = max(0, round($availableRevenue - $availableCommission, 2));
        $onHoldNetEarnings = max(0, round($netEarnings - $availableNet, 2));

        $totalPaidOut = (float) Payout::query()
            ->where('provider_id', $userId)
            ->whereIn('status', ['PAID', 'paid'])
            ->sum('amount');

        $availableBalance = max(0, round($availableNet - $totalPaidOut, 2));

        return [
            'totalRevenue' => $totalRevenue,
            'commission' => $commission,
            'netEarnings' => $netEarnings,
            'availableBalance' => $availableBalance,
            'onHoldNetEarnings' => $onHoldNetEarnings,
        ];
    }
}
