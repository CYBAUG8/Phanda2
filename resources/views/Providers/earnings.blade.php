@extends('Providers.layout')

@section('content')
<div x-data="earningsPage()" x-init="init()" class="px-6 py-6 relative">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Earnings</h1>
            <p class="text-sm text-gray-500">Track your income and payout history</p>
        </div>

        <button
            @click="withdrawOpen = true"
            class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-xl shadow-sm transition"
        >
            Withdraw Funds
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-xl p-6">
            <p class="text-sm opacity-80">Available Balance (48h hold applied)</p>
            <h2 class="text-2xl font-bold mt-2">
                R <span x-text="money(availableBalance)"></span>
            </h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500">Total Revenue</p>
            <h2 class="text-xl font-bold mt-2">R <span x-text="money(totalRevenue)"></span></h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500">Platform Commission (10%)</p>
            <h2 class="text-xl font-bold text-red-500 mt-2">- R <span x-text="money(commission)"></span></h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500">Net Earnings</p>
            <h2 class="text-xl font-bold text-green-600 mt-2">
                R <span x-text="money(netEarnings)"></span>
            </h2>
        </div>
    
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500">On Hold (48h)</p>
            <h2 class="text-xl font-bold text-amber-600 mt-2">R <span x-text="money(onHoldNetEarnings)"></span></h2>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4">Processing Requests</h3>

        <template x-if="processingRequests.length === 0">
            <p class="text-gray-400">No withdrawal requests in progress.</p>
        </template>

        <template x-for="request in processingRequests" :key="request.id">
            <div class="flex justify-between items-center border-b py-3">
                <div>
                    <span class="font-medium">R <span x-text="money(request.amount)"></span></span>
                    <p class="text-xs text-gray-400" x-text="request.created_at"></p>
                </div>
                <span class="text-yellow-500 text-sm font-semibold">Pending</span>
            </div>
        </template>
    </div>

    <div
        x-show="withdrawOpen"
        x-transition
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        style="display:none"
    >
        <div
            @click.away="!loading && (withdrawOpen = false)"
            class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative"
        >
            <h2 class="text-xl font-bold mb-4">Withdraw Funds</h2>

            <form x-ref="withdrawForm" method="POST" action="{{ route('provider.earnings.withdraw') }}" class="space-y-3">
                @csrf

                <div>
                    <label class="text-sm text-gray-600">Select Bank</label>
                    <select
                        name="bank"
                        x-model="bank"
                        class="w-full mt-1 border rounded-lg px-3 py-2"
                        required
                    >
                        <option value="">Choose Bank</option>
                        <option value="FNB">FNB</option>
                        <option value="Standard Bank">Standard Bank</option>
                        <option value="ABSA">ABSA</option>
                        <option value="Nedbank">Nedbank</option>
                        <option value="Capitec">Capitec</option>
                    </select>
                </div>

                <div>
                    <input
                        type="text"
                        inputmode="numeric"
                        name="account_number"
                        placeholder="Account Number"
                        x-model="accountNumber"
                        class="w-full border rounded-lg px-3 py-2"
                        required
                    >
                </div>

                <div>
                    <input
                        type="text"
                        name="account_holder"
                        placeholder="Account Holder"
                        x-model="accountHolder"
                        class="w-full border rounded-lg px-3 py-2"
                        required
                    >
                </div>

                <div>
                    <input
                        type="number"
                        step="0.01"
                        min="1"
                        name="amount"
                        placeholder="Amount"
                        x-model.number="amount"
                        class="w-full border rounded-lg px-3 py-2"
                        required
                    >
                </div>

                <div class="bg-gray-50 rounded-lg p-3 text-sm">
                    <p>
                        Platform Fee (10%):
                        <span class="text-red-500">- R <span x-text="money(platformFee)"></span></span>
                    </p>
                    <p class="font-semibold mt-1">
                        You Will Receive:
                        R <span x-text="money(receiveAmount)"></span>
                    </p>
                </div>

                <p x-show="amount > availableBalance" class="text-red-500 text-sm">
                    Amount cannot exceed available balance.
                </p>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="withdrawOpen = false"
                        :disabled="loading"
                        class="px-4 py-2 bg-gray-200 rounded-lg"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        @click="submitWithdraw"
                        :disabled="!canSubmit || loading"
                        class="px-4 py-2 text-white rounded-lg flex items-center gap-2"
                        :class="!canSubmit || loading ? 'bg-gray-400' : 'bg-orange-500 hover:bg-orange-600'"
                    >
                        <svg
                            x-show="loading"
                            class="animate-spin h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
                        </svg>

                        <span x-text="loading ? 'Processing...' : 'Send Request'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        x-show="showToast"
        x-transition
        class="fixed bottom-6 right-6 bg-green-600 text-white px-6 py-3 rounded-xl shadow-lg"
        style="display:none"
    >
        Request Submitted Successfully
    </div>
</div>

<script>
function earningsPage() {
    return {
        withdrawOpen: false,
        loading: false,
        showToast: false,

        availableBalance: Number(@json($availableBalance ?? 0)),
        totalRevenue: Number(@json($totalRevenue ?? 0)),
        commission: Number(@json($commission ?? 0)),
        netEarnings: Number(@json($netEarnings ?? 0)),

        processingRequests: @json(collect($processingRequests ?? [])->map(fn($request) => [
            'id' => $request->payout_id,
            'amount' => (float) $request->amount,
            'created_at' => optional($request->created_at)->format('d M Y H:i')
        ])->values()),

        bank: @json(old('bank', '')),
        accountNumber: @json(old('account_number', '')),
        accountHolder: @json(old('account_holder', '')),
        amount: Number(@json(old('amount', 0))),

        init() {
            this.showToast = @json((bool) ($withdrawalSuccess ?? false));
            this.withdrawOpen = @json($errors->any());

            if (this.showToast) {
                setTimeout(() => {
                    this.showToast = false;
                }, 3000);
            }
        },

        money(value) {
            const amount = Number(value || 0);
            return amount.toFixed(2);
        },

        get platformFee() {
            return (Number(this.amount) || 0) * 0.10;
        },

        get receiveAmount() {
            const amount = Number(this.amount) || 0;
            return Math.max(amount - this.platformFee, 0);
        },

        get canSubmit() {
            const amount = Number(this.amount) || 0;
            return Boolean(this.bank)
                && Boolean(String(this.accountNumber).trim())
                && Boolean(String(this.accountHolder).trim())
                && amount > 0
                && amount <= this.availableBalance;
        },

        submitWithdraw() {
            if (!this.canSubmit || this.loading) {
                return;
            }

            this.loading = true;

            setTimeout(() => {
                this.$refs.withdrawForm.submit();
            }, 1500);
        }
    }
}
</script>
@endsection




