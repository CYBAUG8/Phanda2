@extends('providers.layout')

@section('content')

<div x-data="earningsPage()" class="px-6 py-6 relative">

<!-- HEADER -->
<section class="bg-white border rounded-lg p-8 shadow-sm mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Earnings</h1>
            <p class="text-sm text-gray-500">Track your income and payout history</p>
        </div>

        <button @click="withdrawOpen = true"
            class="bg-orange-600 hover:bg-orange-700 text-white px-5 py-2 rounded-xl shadow-sm transition">
            Withdraw Funds
        </button>
    </div>
</section>

<!-- SUMMARY -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

    <div class="bg-gradient-to-r from-orange-600 to-orange-600 text-white rounded-xl p-6">
        <p class="text-sm opacity-80">Available Balance</p>
        <h2 class="text-2xl font-bold mt-2">
            R <span x-text="parseFloat(availableBalance || 0).toFixed(2)"></span>
        </h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-sm text-gray-500">Total Revenue</p>
        <h2 class="text-xl font-bold mt-2">
            R {{ number_format($totalRevenue ??0,2) }}
        </h2>
    </div>

</div>

<!-- PAYOUTS -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold mb-4">Payout Requests</h3>

    <div class="flex gap-4 mb-4">
        <button @click="activeTab = 'SCHEDULED'"
            :class="{'border-b-2 border-orange-600 font-semibold': activeTab==='SCHEDULED'}"
            class="px-3 py-1 text-sm text-gray-600">Pending</button>

        <button @click="activeTab = 'PAID'"
            :class="{'border-b-2 border-green-600 font-semibold': activeTab==='PAID'}"
            class="px-3 py-1 text-sm text-gray-600">Paid</button>

        <button @click="activeTab = 'FAILED'"
            :class="{'border-b-2 border-red-600 font-semibold': activeTab==='FAILED'}"
            class="px-3 py-1 text-sm text-gray-600">Failed</button>
    </div>

    <template x-if="filteredPayouts.length === 0">
        <p class="text-gray-400">No requests in this category.</p>
    </template>

    <template x-for="request in filteredPayouts" :key="request.id">
        <div class="flex justify-between border-b py-3">
            <span class="font-medium">
                R <span x-text="parseFloat(request.amount || 0).toFixed(2)"></span>
            </span>
            <span class="text-sm font-semibold" x-text="request.status"></span>
        </div>
    </template>
</div>

<!-- MODAL -->
<div x-show="withdrawOpen" x-transition x-cloak
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

    <div @click.away="withdrawOpen = false"
        class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

        <h2 class="text-xl font-bold mb-4">Withdraw Funds</h2>

        <div class="mb-3">
            <select x-model="bank" class="w-full border rounded-lg px-3 py-2">
                <option value="">Choose Bank</option>
                <option>FNB</option>
                <option>Standard Bank</option>
                <option>ABSA</option>
                <option>Nedbank</option>
                <option>Capitec</option>
            </select>
            <p x-show="!bank && triedSubmit" class="text-red-500 text-sm mt-1">
                Bank is required
            </p>
        </div>

        <div class="mb-3">
            <input type="number" placeholder="Account Number" required x-model="accountNumber"
                class="w-full border rounded-lg px-3 py-2">
            <p x-show="!accountNumber && triedSubmit" class="text-red-500 text-sm mt-1">
                Account number is required
            </p>
        </div>

        <div class="mb-3">
            <input type="text" placeholder="Account Holder" required x-model="accountHolder"
                class="w-full border rounded-lg px-3 py-2">
            <p x-show="!accountHolder && triedSubmit" class="text-red-500 text-sm mt-1">
                Account holder is required
            </p>
        </div>

        <div class="mb-3">
            <input type="number" placeholder="Amount" x-model="amount"
                class="w-full border rounded-lg px-3 py-2">
            <p x-show="!amount && triedSubmit" class="text-red-500 text-sm mt-1">
                Amount is required
            </p>

            <p x-show="parseFloat(amount || 0) <= 0 && triedSubmit" class="text-red-500 text-sm mt-1">
                Amount must be greater than 0
            </p>

            <p x-show="parseFloat(amount || 0) > parseFloat(availableBalance || 0) && triedSubmit"
                class="text-red-500 text-sm mt-1">
                Amount exceeds available balance
            </p>
        </div>

        <!-- COMMISSION ONLY -->
        <div class="bg-gray-50 rounded-lg p-3 text-sm mb-3">
            <p class="font-semibold">
                Commission for this withdrawal: 
                R <span x-text="(parseFloat(amount || 0) * 0.10).toFixed(2)"></span>
            </p>
        </div>

        <!-- BUTTONS -->
        <div class="flex justify-end gap-3">
            <button @click="withdrawOpen = false"
                class="px-4 py-2 bg-gray-200 rounded-lg">Cancel</button>

            <button @click="submitWithdraw"
                :disabled="parseFloat(amount || 0) > parseFloat(availableBalance || 0) || parseFloat(amount || 0) <= 0 || loading"
                class="px-4 py-2 text-white rounded-lg"
                :class="parseFloat(amount || 0) > parseFloat(availableBalance || 0) || parseFloat(amount || 0) <= 0 || loading ? 'bg-gray-400' : 'bg-orange-600'">
                <span x-text="loading ? 'Processing...' : 'Send Request'"></span>
            </button>
        </div>

    </div>
</div>

</div>

<script>
function earningsPage() {
    return {
        withdrawOpen: false,
        loading: false,
        triedSubmit: false,

        availableBalance: parseFloat({{ $availableBalance }}),

        bank: '',
        accountNumber: '',
        accountHolder: '',
        amount: '',

        payouts: @json($processingRequests ?? []),
        activeTab: 'SCHEDULED',

        get filteredPayouts() {
            return this.payouts
            .filter(p => p.status.toUpperCase() === this.activeTab);
        },

        submitWithdraw() {
            this.triedSubmit = true; 

            let amount = parseFloat(this.amount) || 0;
            let available = parseFloat(this.availableBalance) || 0;

            // VALIDATION
            if (!this.bank || !this.accountNumber || !this.accountHolder || !this.amount || amount <= 0) {
                return;
            }

            if (amount > available) {
                return;
            }

            let commission = amount * 0.10;
            let total = amount + commission;

            this.loading = true;

            fetch("{{ route('providers.payout.request') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    amount: amount,
                    bank: this.bank,
                    account_number: this.accountNumber,
                    account_holder: this.accountHolder
                })
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                this.withdrawOpen = false;

                this.payouts.unshift({
                    id: data.payout.payout_id,
                    amount: parseFloat(data.payout.amount),
                    status: data.payout.status.toUpperCase()
                });

                // deduct amount + commission
                this.availableBalance = available - total;

                // reset form
                this.amount = '';
                this.bank = '';
                this.accountNumber = '';
                this.accountHolder = '';
                this.triedSubmit = false;
            });
        },

        init() {
            setInterval(() => {
                fetch("{{ route('providers.payout.refresh') }}")
                    .then(res => res.json())
                    .then(data => {
                        this.payouts = data.payouts.map(p => ({
                            ...p,
                            amount: parseFloat(p.amount),
                            status: p.status.toUpperCase()
                        }));

                        let totalWithdrawn = this.payouts
                            .filter(p => ['SCHEDULED','PAID'].includes(p.status))
                            .reduce((sum, p) => sum + p.amount, 0);

                        this.availableBalance = parseFloat(data.availableBalance);
                    });

            }, 10000);
        }
    }
}
</script>
@endsection
