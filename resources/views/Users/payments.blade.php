@extends('Users.layout')

@section('content')
    @php
        $shouldOpenModal = $errors->hasAny([
            'card_holder_name',
            'card_number',
            'expiry_month',
            'expiry_year',
            'cvv',
        ]);

        $expiryDisplayOld = old('expiry_display', '');
        if ($expiryDisplayOld === '' && old('expiry_month') && old('expiry_year')) {
            $expiryDisplayOld = str_pad((string) old('expiry_month'), 2, '0', STR_PAD_LEFT) . '/' . substr((string) old('expiry_year'), -2);
        }
    @endphp

    <div class="user-page-shell space-y-6">
        <section class="user-page-header">
            <div>
                <h1>Payments</h1>
                <p class="user-page-subtitle">Manage saved cards and review payment history for your bookings.</p>
            </div>
            <button type="button" class="ui-btn-primary" data-open-modal="add-card">
                <i class="fas fa-plus"></i>
                <span>Add Payment Method</span>
            </button>
        </section>

        @include('partials.ui.flash')

        <div class="payui-shell">
        <section class="payui-section">
            <div class="payui-section__head">
                <h2>Saved Payment Methods</h2>
            </div>

            @if($cards->isEmpty())
                <div class="payui-empty">
                    <p>No payment methods saved yet.</p>
                    <button type="button" class="payui-add-btn payui-add-btn--small" data-open-modal="add-card">
                        Add Payment Method
                    </button>
                </div>
            @else
                <div class="payui-cards-grid">
                    @foreach($cards as $card)
                        <article class="payui-card {{ $card->is_default ? 'is-default' : '' }}">
                            @if($card->is_default)
                                <span class="payui-default-pill">Default</span>
                            @endif

                            <div class="payui-card__top">
                                <strong>{{ strtoupper($card->brand) }}</strong>
                                <span>•••• {{ $card->last_four }}</span>
                            </div>

                            <p class="payui-card__number">•••• •••• •••• {{ $card->last_four }}</p>

                            <div class="payui-card__meta">
                                <span>Expires {{ str_pad((string) $card->expiry_month, 2, '0', STR_PAD_LEFT) }}/{{ substr((string) $card->expiry_year, -2) }}</span>
                                <span>{{ $card->holder_name }}</span>
                            </div>

                            <div class="payui-card__actions">
                                @if(!$card->is_default)
                                    <form method="POST" action="{{ route('users.payments.methods.default', $card->payment_method_id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="payui-secondary-btn">Set as Default</button>
                                    </form>
                                @endif

                                <form
                                    method="POST"
                                    action="{{ route('users.payments.methods.destroy', $card->payment_method_id) }}"
                                    data-confirm="Are you sure you want to remove this payment method?"
                                    data-confirm-title="Remove payment method"
                                    data-confirm-text="Remove"
                                    data-confirm-variant="danger"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="payui-danger-btn">Remove</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="payui-section">
            <div class="payui-section__head">
                <h2>Payment History</h2>
            </div>

            @if($payments->isEmpty())
                <div class="payui-empty">
                    <p>No payments made yet.</p>
                </div>
            @else
                <div class="payui-table-wrap">
                    <table class="payui-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                @php
                                    $brand = strtoupper((string) ($payment->paymentMethod?->brand ?? data_get($payment->metadata, 'card_brand', $payment->method)));
                                    $lastFour = $payment->paymentMethod?->last_four ?? data_get($payment->metadata, 'card_last_four');
                                    $methodLabel = ucfirst($payment->method);
                                    if ($payment->method === 'card' && $lastFour) {
                                        $methodLabel = $brand . ' ••••' . $lastFour;
                                    }

                                    $statusLabel = match ($payment->status) {
                                        'paid' => 'Completed',
                                        'pending' => 'Pending',
                                        'refunded' => 'Refunded',
                                        default => 'Failed',
                                    };

                                    $statusClass = match ($payment->status) {
                                        'paid' => 'is-completed',
                                        'pending' => 'is-pending',
                                        'refunded' => 'is-refunded',
                                        default => 'is-failed',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $payment->created_at?->timezone('Africa/Johannesburg')->format('d F Y') }}</td>
                                    <td>{{ $payment->booking->service->title ?? 'Service Payment' }}</td>
                                    <td>R{{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>{{ $methodLabel }}</td>
                                    <td>
                                        <span class="payui-status {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrap">
                    {{ $payments->links() }}
                </div>
            @endif
        </section>
        </div>
    </div>

    <div id="payuiAddCardModal" class="payui-modal {{ $shouldOpenModal ? 'is-open' : '' }}">
        <div class="payui-modal__backdrop" data-close-modal></div>
        <div class="payui-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="payuiAddCardTitle">
            <div class="payui-modal__head">
                <h3 id="payuiAddCardTitle">Add Payment Method</h3>
                <button type="button" class="payui-modal__close" data-close-modal>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('users.payments.methods.store') }}" id="payuiAddCardForm" class="payui-modal__form">
                @csrf

                <div class="payui-field">
                    <label for="payuiCardNumber">Card Number</label>
                    <input
                        type="text"
                        id="payuiCardNumber"
                        name="card_number"
                        value="{{ old('card_number') }}"
                        placeholder="0000 0000 0000 0000"
                        maxlength="23"
                        class="@error('card_number') is-invalid @enderror"
                        required
                    >
                    @error('card_number')
                        <p class="payui-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="payui-field">
                    <label for="payuiCardName">Name on Card</label>
                    <input
                        type="text"
                        id="payuiCardName"
                        name="card_holder_name"
                        value="{{ old('card_holder_name') }}"
                        placeholder="John Doe"
                        class="@error('card_holder_name') is-invalid @enderror"
                        required
                    >
                    @error('card_holder_name')
                        <p class="payui-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="payui-field-grid">
                    <div class="payui-field">
                        <label for="payuiExpiry">Expiry (MM/YY)</label>
                        <input
                            type="text"
                            id="payuiExpiry"
                            name="expiry_display"
                            value="{{ $expiryDisplayOld }}"
                            placeholder="MM/YY"
                            maxlength="5"
                            class="@error('expiry_month') is-invalid @enderror @error('expiry_year') is-invalid @enderror"
                            required
                        >
                        @error('expiry_month')
                            <p class="payui-error">{{ $message }}</p>
                        @enderror
                        @error('expiry_year')
                            <p class="payui-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="payui-field">
                        <label for="payuiCvv">CVC</label>
                        <input
                            type="text"
                            id="payuiCvv"
                            name="cvv"
                            value=""
                            placeholder="123"
                            maxlength="4"
                            class="@error('cvv') is-invalid @enderror"
                            required
                        >
                        @error('cvv')
                            <p class="payui-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <input type="hidden" name="expiry_month" id="payuiExpiryMonth" value="{{ old('expiry_month') }}">
                <input type="hidden" name="expiry_year" id="payuiExpiryYear" value="{{ old('expiry_year') }}">

                <label class="payui-check">
                    <input type="checkbox" name="set_as_default" value="1" {{ old('set_as_default') ? 'checked' : '' }}>
                    <span>Set as default card</span>
                </label>

                <div class="payui-modal__actions">
                    <button type="button" class="payui-secondary-btn" data-close-modal>Cancel</button>
                    <button type="submit" class="payui-add-btn payui-add-btn--small">Add Card</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('payuiAddCardModal');
    const openButtons = document.querySelectorAll('[data-open-modal="add-card"]');
    const closeButtons = modal ? modal.querySelectorAll('[data-close-modal]') : [];
    const body = document.body;
    const cardNumberInput = document.getElementById('payuiCardNumber');
    const expiryInput = document.getElementById('payuiExpiry');
    const cvvInput = document.getElementById('payuiCvv');
    const expiryMonthInput = document.getElementById('payuiExpiryMonth');
    const expiryYearInput = document.getElementById('payuiExpiryYear');
    const addCardForm = document.getElementById('payuiAddCardForm');

    const openModal = () => {
        if (!modal) {
            return;
        }
        modal.classList.add('is-open');
        body.classList.add('payui-modal-open');
    };

    const closeModal = () => {
        if (!modal) {
            return;
        }
        modal.classList.remove('is-open');
        body.classList.remove('payui-modal-open');
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', openModal);
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    if (modal && modal.classList.contains('is-open')) {
        body.classList.add('payui-modal-open');
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', (event) => {
            const digits = event.target.value.replace(/\D/g, '').slice(0, 19);
            event.target.value = digits.replace(/(.{4})/g, '$1 ').trim();
        });
    }

    if (expiryInput) {
        expiryInput.addEventListener('input', (event) => {
            const digits = event.target.value.replace(/\D/g, '').slice(0, 4);

            if (digits.length >= 3) {
                event.target.value = digits.slice(0, 2) + '/' + digits.slice(2);
                return;
            }

            event.target.value = digits;
        });
    }

    if (cvvInput) {
        cvvInput.addEventListener('input', (event) => {
            event.target.value = event.target.value.replace(/\D/g, '').slice(0, 4);
        });
    }

    if (addCardForm && expiryInput && expiryMonthInput && expiryYearInput) {
        addCardForm.addEventListener('submit', () => {
            const digits = expiryInput.value.replace(/\D/g, '').slice(0, 4);

            if (digits.length >= 2) {
                expiryMonthInput.value = digits.slice(0, 2);
            } else {
                expiryMonthInput.value = '';
            }

            if (digits.length === 4) {
                expiryYearInput.value = '20' + digits.slice(2, 4);
            } else {
                expiryYearInput.value = '';
            }
        });
    }
});
</script>
@endpush
