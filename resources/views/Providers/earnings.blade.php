@extends('Providers.layout')

@section('content')
@php
    $processingRequestsPayload = collect($processingRequests ?? [])
        ->map(function ($request) {
            return [
                'id' => $request->payout_id,
                'amount' => (float) $request->amount,
                'created_at' => optional($request->created_at)->format('d M Y H:i'),
            ];
        })
        ->values();
@endphp

<div x-data="providerEarningsPage()" x-init="init()" class="provider-page-shell space-y-6">
    <section class="provider-page-header">
        <div>
            <h1>Earnings</h1>
            <p class="provider-page-subtitle">Track completed revenue, payout requests, and funds currently on hold.</p>
        </div>
        <button type="button" @click="openWithdraw()" class="ui-btn-primary min-h-11 justify-center px-4 py-2.5">
            <i class="fa-solid fa-money-bill-transfer"></i>
            <span>Withdraw Funds</span>
        </button>
    </section>

    @include('partials.ui.flash')

    <section class="provider-metrics-grid" aria-label="Earnings metrics">
        <article class="provider-metric-card">
            <p class="provider-metric-label">Available Balance</p>
            <p class="provider-metric-value text-orange-700">R<span x-text="money(availableBalance)"></span></p>
            <p class="mt-1 text-xs text-slate-500">Only cleared payments older than 48 hours are available.</p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Total Revenue</p>
            <p class="provider-metric-value">R<span x-text="money(totalRevenue)"></span></p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Commission (10%)</p>
            <p class="provider-metric-value text-rose-700">R<span x-text="money(commission)"></span></p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Net Earnings</p>
            <p class="provider-metric-value text-emerald-700">R<span x-text="money(netEarnings)"></span></p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">On Hold (48h)</p>
            <p class="provider-metric-value text-amber-700">R<span x-text="money(onHoldNetEarnings)"></span></p>
        </article>
    </section>

    <section class="ui-card p-5">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h2 class="provider-section-title">Processing Requests</h2>
                <p class="provider-section-copy">Submitted withdrawals stay here until finance review is completed.</p>
            </div>
        </div>

        <template x-if="processingRequests.length === 0">
            <div class="provider-empty-inline">
                No withdrawal requests are currently in progress.
            </div>
        </template>

        <template x-if="processingRequests.length > 0">
            <div class="space-y-2">
                <template x-for="request in processingRequests" :key="request.id">
                    <article class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">R<span x-text="money(request.amount)"></span></p>
                            <p class="text-xs text-slate-500" x-text="request.created_at"></p>
                        </div>
                        <span class="provider-status-badge provider-status-pending">Pending Review</span>
                    </article>
                </template>
            </div>
        </template>
    </section>

    <div
        x-show="withdrawOpen"
        x-cloak
        x-on:keydown.escape.window="closeWithdraw()"
        class="fixed inset-0 z-50 overflow-y-auto p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="withdrawTitle"
    >
        <div class="flex min-h-full items-center justify-center">
            <div class="fixed inset-0 bg-slate-950/50" @click="closeWithdraw()"></div>
            <div class="provider-modal-panel relative z-10 w-full max-w-lg">
                <div class="provider-modal-header">
                    <div>
                        <h3 id="withdrawTitle" class="text-lg font-semibold text-slate-900">Withdraw Funds</h3>
                        <p class="mt-1 text-xs text-slate-500">Transfer available funds to your bank account.</p>
                    </div>
                    <button
                        type="button"
                        class="text-slate-500 hover:text-slate-700"
                        @click="closeWithdraw()"
                        :disabled="loading"
                        aria-label="Close withdraw modal"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form x-ref="withdrawForm" method="POST" action="{{ route('provider.earnings.withdraw') }}" class="provider-modal-body space-y-4">
                    @csrf

                    <div>
                        <label for="bank" class="provider-label normal-case tracking-normal text-slate-700">Select Bank</label>
                        <select id="bank" name="bank" x-model="bank" class="provider-select @error('bank') border-rose-400 ring-2 ring-rose-100 @enderror" @error('bank') aria-invalid="true" @enderror required>
                            <option value="">Choose bank</option>
                            <option value="FNB">FNB</option>
                            <option value="Standard Bank">Standard Bank</option>
                            <option value="ABSA">ABSA</option>
                            <option value="Nedbank">Nedbank</option>
                            <option value="Capitec">Capitec</option>
                        </select>
                        @error('bank')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="accountNumber" class="provider-label normal-case tracking-normal text-slate-700">Account Number</label>
                            <input
                                id="accountNumber"
                                type="text"
                                inputmode="numeric"
                                name="account_number"
                                x-model="accountNumber"
                                class="provider-input @error('account_number') border-rose-400 ring-2 ring-rose-100 @enderror"
                                @error('account_number') aria-invalid="true" @enderror
                                placeholder="Enter account number"
                                required
                            >
                            @error('account_number')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="amount" class="provider-label normal-case tracking-normal text-slate-700">Amount (R)</label>
                            <input
                                id="amount"
                                type="number"
                                step="0.01"
                                min="1"
                                name="amount"
                                x-model.number="amount"
                                class="provider-input @error('amount') border-rose-400 ring-2 ring-rose-100 @enderror"
                                @error('amount') aria-invalid="true" @enderror
                                placeholder="0.00"
                                required
                            >
                            @error('amount')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="accountHolder" class="provider-label normal-case tracking-normal text-slate-700">Account Holder</label>
                        <input
                            id="accountHolder"
                            type="text"
                            name="account_holder"
                            x-model="accountHolder"
                            class="provider-input @error('account_holder') border-rose-400 ring-2 ring-rose-100 @enderror"
                            @error('account_holder') aria-invalid="true" @enderror
                            placeholder="Name on account"
                            required
                        >
                        @error('account_holder')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                        <p class="text-slate-600">
                            Platform fee (10%):
                            <span class="font-semibold text-rose-700">R<span x-text="money(platformFee)"></span></span>
                        </p>
                        <p class="mt-1 text-slate-900">
                            You will receive:
                            <span class="font-semibold">R<span x-text="money(receiveAmount)"></span></span>
                        </p>
                    </div>

                    <p x-show="amount > availableBalance" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
                        Amount cannot exceed your available balance.
                    </p>
                </form>

                <div class="provider-modal-footer">
                    <button type="button" class="ui-btn-secondary px-4 py-2" @click="closeWithdraw()" :disabled="loading">Cancel</button>
                    <button
                        type="button"
                        @click="submitWithdraw()"
                        :disabled="!canSubmit || loading"
                        class="ui-btn-primary min-h-11 px-4 py-2 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <i x-show="loading" class="fa-solid fa-spinner animate-spin"></i>
                        <span x-text="loading ? 'Submitting...' : 'Submit Request'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function providerEarningsPage() {
    return {
        withdrawOpen: false,
        loading: false,
        availableBalance: Number(@json($availableBalance ?? 0)),
        totalRevenue: Number(@json($totalRevenue ?? 0)),
        commission: Number(@json($commission ?? 0)),
        netEarnings: Number(@json($netEarnings ?? 0)),
        onHoldNetEarnings: Number(@json($onHoldNetEarnings ?? 0)),
        processingRequests: @js($processingRequestsPayload),
        bank: @json(old('bank', '')),
        accountNumber: @json(old('account_number', '')),
        accountHolder: @json(old('account_holder', '')),
        amount: Number(@json(old('amount', 0))),
        init() {
            this.withdrawOpen = @json($errors->any());
            const withdrawalSuccess = @json((bool) ($withdrawalSuccess ?? false));
            if (withdrawalSuccess) {
                window.uiToast('Withdrawal request submitted successfully.', 'success');
            }
        },
        money(value) {
            return Number(value || 0).toFixed(2);
        },
        get platformFee() {
            return (Number(this.amount) || 0) * 0.10;
        },
        get receiveAmount() {
            const gross = Number(this.amount) || 0;
            return Math.max(gross - this.platformFee, 0);
        },
        get canSubmit() {
            const amount = Number(this.amount) || 0;
            return Boolean(this.bank)
                && Boolean(String(this.accountNumber).trim())
                && Boolean(String(this.accountHolder).trim())
                && amount > 0
                && amount <= this.availableBalance;
        },
        openWithdraw() {
            if (this.loading) {
                return;
            }
            this.withdrawOpen = true;
        },
        closeWithdraw() {
            if (this.loading) {
                return;
            }
            this.withdrawOpen = false;
        },
        submitWithdraw() {
            if (!this.canSubmit || this.loading) {
                window.uiToast('Enter valid withdrawal details before submitting.', 'warning');
                return;
            }

            this.loading = true;
            this.$refs.withdrawForm.submit();
        },
    };
}
</script>
@endpush
