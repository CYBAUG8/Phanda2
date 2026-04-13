@extends('Users.layout')

@section('content')
    @php
        $defaultCardId = optional($cards->firstWhere('is_default', true))->payment_method_id;
        $selectedCardId = old('payment_method_id');
        if ($selectedCardId === null) {
            $selectedCardId = $defaultCardId ?? '';
        }
    @endphp

    <div class="user-page-shell space-y-6">
        <section class="user-page-header">
            <div>
                <h1>Complete Payment</h1>
                <p class="user-page-subtitle">Securely pay for your confirmed service booking.</p>
            </div>
            <a href="{{ route('users.bookings') }}" class="ui-btn-secondary">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Bookings</span>
            </a>
        </section>

        @include('partials.ui.flash')

        <div class="payments-layout-grid">
        <section class="ui-card payment-checkout-card">
            <div class="checkout-booking-summary">
                <div>
                    <p class="checkout-label">Booking</p>
                    <h3>{{ $booking->service->title ?? 'Service' }}</h3>
                    <p class="checkout-muted">{{ $booking->booking_code }}</p>
                </div>
                <div class="checkout-amount">
                    <p class="checkout-label">Amount</p>
                    <h3>{{ $booking->formatted_price }}</h3>
                </div>
            </div>

            <form
                id="checkoutPaymentForm"
                method="POST"
                action="{{ route('users.payments.pay', $booking->id) }}"
                class="checkout-form"
            >
                @csrf

                <div class="form-group">
                    <label for="method"><i class="fas fa-money-check-dollar"></i> Payment Method</label>
                    <select name="method" id="method" class="user-select" required>
                        @foreach($methods as $methodKey => $methodLabel)
                            <option value="{{ $methodKey }}" {{ old('method', 'card') === $methodKey ? 'selected' : '' }}>
                                {{ $methodLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="cardPaymentSection">
                    @if($cards->count() > 0)
                        <div class="checkout-section">
                            <label class="checkout-section__title">Choose Saved Card</label>
                            <div class="saved-cards-list">
                                @foreach($cards as $card)
                                    <label class="saved-card-option">
                                        <input
                                            type="radio"
                                            name="payment_method_id"
                                            value="{{ $card->payment_method_id }}"
                                            {{ (string) $selectedCardId === (string) $card->payment_method_id ? 'checked' : '' }}
                                        >
                                        <span class="saved-card-option__body">
                                            <span class="saved-card-option__top">
                                                <strong>{{ strtoupper($card->brand) }}</strong>
                                                @if($card->is_default)
                                                    <span class="user-status-badge user-status-payment-paid">Default</span>
                                                @endif
                                            </span>
                                            <span class="saved-card-option__meta">
                                                **** **** **** {{ $card->last_four }} · {{ str_pad((string) $card->expiry_month, 2, '0', STR_PAD_LEFT) }}/{{ $card->expiry_year }}
                                            </span>
                                        </span>
                                    </label>
                                @endforeach

                                <label class="saved-card-option saved-card-option--new">
                                    <input
                                        type="radio"
                                        name="payment_method_id"
                                        value=""
                                        {{ (string) $selectedCardId === '' ? 'checked' : '' }}
                                    >
                                    <span class="saved-card-option__body">
                                        <span class="saved-card-option__top">
                                            <strong>Use a new card</strong>
                                        </span>
                                        <span class="saved-card-option__meta">Enter a different card below.</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="payment_method_id" value="">
                    @endif

                    <div id="newCardSection" class="checkout-section">
                        <label class="checkout-section__title">Card Details</label>
                        <div class="checkout-card-grid">
                            <div class="form-group">
                                <label for="card_holder_name"><i class="fas fa-user"></i> Name on Card</label>
                                <input
                                    type="text"
                                    id="card_holder_name"
                                    name="card_holder_name"
                                    class="user-input"
                                    value="{{ old('card_holder_name') }}"
                                    placeholder="Card holder full name"
                                >
                            </div>
                            <div class="form-group">
                                <label for="card_number"><i class="fas fa-credit-card"></i> Card Number</label>
                                <input
                                    type="text"
                                    id="card_number"
                                    name="card_number"
                                    class="user-input"
                                    value="{{ old('card_number') }}"
                                    placeholder="4111 1111 1111 1111"
                                    inputmode="numeric"
                                >
                            </div>
                            <div class="form-group">
                                <label for="expiry_month"><i class="fas fa-calendar-alt"></i> Expiry Month</label>
                                <input
                                    type="number"
                                    id="expiry_month"
                                    name="expiry_month"
                                    class="user-input"
                                    min="1"
                                    max="12"
                                    value="{{ old('expiry_month') }}"
                                    placeholder="MM"
                                >
                            </div>
                            <div class="form-group">
                                <label for="expiry_year"><i class="fas fa-calendar"></i> Expiry Year</label>
                                <input
                                    type="number"
                                    id="expiry_year"
                                    name="expiry_year"
                                    class="user-input"
                                    min="{{ now()->year }}"
                                    max="{{ now()->year + 20 }}"
                                    value="{{ old('expiry_year') }}"
                                    placeholder="YYYY"
                                >
                            </div>
                            <div class="form-group">
                                <label for="cvv"><i class="fas fa-lock"></i> CVV</label>
                                <input
                                    type="password"
                                    id="cvv"
                                    name="cvv"
                                    class="user-input"
                                    placeholder="123"
                                    inputmode="numeric"
                                >
                            </div>
                        </div>

                        <div class="checkout-checkboxes">
                            <label class="checkbox-inline">
                                <input type="checkbox" name="save_card" value="1" {{ old('save_card') ? 'checked' : '' }}>
                                <span>Save this card for future bookings</span>
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="set_as_default" value="1" {{ old('set_as_default') ? 'checked' : '' }}>
                                <span>Set as default card</span>
                            </label>
                        </div>
                    </div>
                </div>

                <p class="booking-card__notes mt-0">
                    <i class="fas fa-shield-alt"></i>
                    Demo mode: card details are never charged. Only masked data is saved.
                </p>

                <div class="checkout-actions">
                    <button type="submit" class="ui-btn-primary min-h-11">
                        <i class="fas fa-check"></i> Complete Payment
                    </button>

                    <button
                        type="button"
                        class="ui-btn-danger min-h-11"
                        onclick="submitFailure()"
                    >
                        <i class="fas fa-times"></i> Simulate Failure
                    </button>

                    <a href="{{ route('users.bookings') }}" class="ui-btn-secondary min-h-11">
                        <i class="fas fa-arrow-left"></i> Back to Bookings
                    </a>
                </div>
            </form>
        </section>

        <aside class="ui-card payment-history-preview">
            <div class="payment-history-preview__header">
                <h3>Recent Payments</h3>
                <a href="{{ route('users.payments.index') }}" class="btn-outline btn-sm">View Full History</a>
            </div>

            @if($recentPayments->isEmpty())
                <p class="checkout-muted">No payments yet. Completed payments will appear here.</p>
            @else
                <div class="payment-history-preview__list">
                    @foreach($recentPayments as $payment)
                        @php
                            $brand = strtoupper((string) ($payment->paymentMethod?->brand ?? data_get($payment->metadata, 'card_brand', $payment->method)));
                            $lastFour = $payment->paymentMethod?->last_four ?? data_get($payment->metadata, 'card_last_four');
                        @endphp
                        <article class="payment-history-preview__item">
                            <div>
                                <strong>{{ $payment->booking->service->title ?? 'Service Payment' }}</strong>
                                <p>
                                    {{ ucfirst($payment->status) }}
                                    @if($lastFour)
                                        · {{ $brand }} ****{{ $lastFour }}
                                    @endif
                                </p>
                            </div>
                            <div class="payment-history-preview__right">
                                <strong>R{{ number_format((float) $payment->amount, 2) }}</strong>
                                <small>{{ $payment->created_at?->timezone('Africa/Johannesburg')->format('d M Y') }}</small>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </aside>
    </div>
    </div>
@endsection

@push('scripts')
<script>
function submitFailure() {
    const form = document.getElementById('checkoutPaymentForm');
    form.action = @json(route('users.payments.simulate-failure', $booking->id));
    form.submit();
}

function toggleCardSection() {
    const methodInput = document.getElementById('method');
    const cardPaymentSection = document.getElementById('cardPaymentSection');
    const newCardSection = document.getElementById('newCardSection');
    const selectedCard = document.querySelector('input[name="payment_method_id"]:checked');

    if (!methodInput || !cardPaymentSection || !newCardSection) {
        return;
    }

    if (methodInput.value !== 'card') {
        cardPaymentSection.style.display = 'none';
        return;
    }

    cardPaymentSection.style.display = '';

    if (!selectedCard || selectedCard.value === '') {
        newCardSection.style.display = '';
        return;
    }

    newCardSection.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    const methodInput = document.getElementById('method');
    const cardOptions = document.querySelectorAll('input[name="payment_method_id"]');
    const paymentForm = document.getElementById('checkoutPaymentForm');
    const defaultAction = paymentForm ? paymentForm.action : null;

    if (paymentForm && defaultAction) {
        paymentForm.addEventListener('submit', () => {
            paymentForm.action = defaultAction;
        });
    }

    if (methodInput) {
        methodInput.addEventListener('change', toggleCardSection);
    }

    cardOptions.forEach((option) => {
        option.addEventListener('change', toggleCardSection);
    });

    toggleCardSection();
});
</script>
@endpush
