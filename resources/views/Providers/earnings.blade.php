@extends('providers.layout')

@section('content')
<div 
    x-data="earningsPage()" 
    class="px-6 py-6 relative"
>

    <!-- ================= HEADER ================= -->
    <section class="bg-white border rounded-lg p-8 shadow-sm mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Earnings</h1>
                <p class="text-sm text-gray-500">Track your income and payout history</p>
            </div>

            <button 
                @click="withdrawOpen = true"
                class="bg-orange-600 hover:bg-orange-700 text-white px-5 py-2 rounded-xl shadow-sm transition"
            >
                Withdraw Funds
            </button>
        </div>
    </section>


    <!-- ================= SUMMARY ================= -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

        <div class="bg-gradient-to-r from-orange-600 to-orange-600 text-white rounded-xl p-6">
            <p class="text-sm opacity-80">Available Balance</p>
            <h2 class="text-2xl font-bold mt-2">
                R <span x-text="availableBalance.toFixed(2)"></span>
            </h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500">Total Revenue</p>
            <h2 class="text-xl font-bold mt-2">
                R {{ number_format($totalRevenue ??0,2) }}
            </h2>
        </div>

    </div>


    <!-- ================= PROCESSING REQUESTS ================= -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4">Payout Requests</h3>

        <div class="flex gap-4 mb-4">
            <button 
                @click="activeTab = 'SCHEDULED'"
                :class="{'border-b-2 border-orange-600 font-semibold': activeTab==='SCHEDULED'}"
                class="px-3 py-1 text-sm text-gray-600 hover:text-orange-600"
            >
                Pending
            </button>
            <button 
                @click="activeTab = 'PAID'"
                :class="{'border-b-2 border-green-600 font-semibold': activeTab==='PAID'}"
                class="px-3 py-1 text-sm text-gray-600 hover:text-green-600"
            >
                Paid
            </button>
            <button 
                @click="activeTab = 'FAILED'"
                :class="{'border-b-2 border-red-600 font-semibold': activeTab==='FAILED'}"
                class="px-3 py-1 text-sm text-gray-600 hover:text-red-600"
            >
                Failed
            </button>
        </div>

        <!-- Requests List -->
        <template x-if="filteredPayouts.length === 0">
            <p class="text-gray-400">No requests in this category.</p>
        </template>

        <template x-for="request in filteredPayouts" :key="request.id">
            <div class="flex justify-between items-center border-b py-3">
                <span class="font-medium">
                    R <span x-text="request.amount.toFixed(2)"></span>
                </span>
                <span 
                    class="text-sm font-semibold"
                    :class="{
                        'text-yellow-500': request.status === 'SCHEDULED',
                        'text-green-500': request.status === 'PAID',
                        'text-red-500': request.status === 'FAILED'
                    }"
                    x-text="request.status"
                ></span>
            </div>
        </template>
    </div>


    <!-- ================= MODAL ================= -->
    <div 
        x-show="withdrawOpen"
        x-transition
        x-cloak
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        style="display:none"
    >
        <div 
            @click.away="withdrawOpen = false"
            class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative"
        >

            <h2 class="text-xl font-bold mb-4">Withdraw Funds</h2>

            <!-- Bank Dropdown -->
            <div class="mb-3">
                <label class="text-sm text-gray-600">Select Bank</label>
                <select 
                    x-model="bank"
                    class="w-full mt-1 border rounded-lg px-3 py-2"
                >
                    <option value="">Choose Bank</option>
                    <option>FNB</option>
                    <option>Standard Bank</option>
                    <option>ABSA</option>
                    <option>Nedbank</option>
                    <option>Capitec</option>
                </select>
            </div>

            <!-- Account Number -->
            <div class="mb-3">
                <input 
                    type="number"
                    placeholder="Account Number"
                    x-model="accountNumber"
                    class="w-full border rounded-lg px-3 py-2"
                >
            </div>

            <!-- Account Holder -->
            <div class="mb-3">
                <input 
                    type="text"
                    placeholder="Account Holder"
                    x-model="accountHolder"
                    class="w-full border rounded-lg px-3 py-2"
                >
            </div>

            <!-- Amount -->
            <div class="mb-3">
                <input 
                    type="number"
                    placeholder="Amount"
                    x-model="amount"
                    class="w-full border rounded-lg px-3 py-2"
                >
            </div>

            <!-- Deduction Preview -->
            <div class="bg-gray-50 rounded-lg p-3 text-sm mb-3">
                <p>Platform Fee (10%): 
                    <span class="text-red-500">
                        - R <span x-text="(amount * 0.10 || 0).toFixed(2)"></span>
                    </span>
                </p>
                <p class="font-semibold mt-1">
                    You Will Receive: 
                    R <span x-text="(amount - (amount * 0.10) || 0).toFixed(2)"></span>
                </p>
            </div>

            <!-- Error -->
            <p 
                x-show="amount > netEarnings"
                class="text-red-500 text-sm mb-3"
            >
                Amount cannot exceed available balance.
            </p>

            <!-- Buttons -->
            <div class="flex justify-end gap-3">
                <button 
                    @click="withdrawOpen = false"
                    class="px-4 py-2 bg-gray-200 rounded-lg"
                >
                    Cancel
                </button>

                <button 
                    @click="submitWithdraw"
                    :disabled="amount > availableBalance || loading"
                    class="px-4 py-2 text-white rounded-lg flex items-center gap-2"
                    :class="amount > availableBalance || loading ? 'bg-gray-400' : 'bg-orange-600 hover:bg-orange-700'"
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

        </div>
    </div>


    <!-- ================= TOAST ================= -->
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

        availableBalance: {{ $availableBalance }}, // current available balance

        bank: '',
        accountNumber: '',
        accountHolder: '',
        amount: 0,

        payouts: @json($processingRequests ?? []),//convert php to json for js

        activeTab: 'SCHEDULED', // default active tab

        // Filter payouts based on selected tab
        get filteredPayouts() {
            return this.payouts.filter(p => p.status === this.activeTab);
        },

        submitWithdraw() {
            if (this.amount > this.availableBalance) {
                alert("Insufficient balance");
                return;
            }

            this.loading = true;

            fetch("{{ route('providers.payout.request') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    bank: this.bank,
                    account_number: this.accountNumber,
                    account_holder: this.accountHolder,
                    amount: this.amount
                })
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                this.withdrawOpen = false;

                // Add new payout request to list
                this.payouts.unshift({
                    id: data.payout.payout_id,
                    amount: parseFloat(this.amount),
                    status: data.payout.status
                });

                // Update available balance
                this.availableBalance -= this.amount;

                this.amount = 0;
                this.showToast = true;

                setTimeout(() => this.showToast = false, 3000);
            });
        },

        // Auto-refresh payouts every 10 seconds
        init() {
            setInterval(() => {
                fetch("{{ route('providers.payout.refresh') }}")
                    .then(res => res.json())
                    .then(data => {
                        this.payouts = data.payouts;

                        // Recalculate available balance
                        let totalPending = this.payouts
                            .filter(p => p.status === 'SCHEDULED')
                            .reduce((sum, p) => sum + parseFloat(p.amount), 0);

                        this.availableBalance = {{ $totalRevenue ?? 0 }} - totalPending;
                    });
            }, 10000);
        }
    }
}
</script>

@endsection