@extends('Users.layout')

@section('content')
    <div class="page-header">
        <h2>Mock Checkout</h2>
        <p>Demo payment screen for confirmed bookings.</p>
    </div>

    @if(session('success'))
        <div class="flash-message flash-message--success">
            <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flash-message flash-message--error">
            <i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="flash-message flash-message--error">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="card" style="max-width: 760px; margin: 0 auto; padding: 24px;">
        <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap; margin-bottom:16px;">
            <div>
                <p style="margin:0 0 6px; color: rgba(11,11,11,0.55);">Booking</p>
                <h3 style="margin:0;">{{ $booking->service->title ?? 'Service' }}</h3>
                <p style="margin:6px 0 0; color: rgba(11,11,11,0.55);">{{ $booking->booking_code }}</p>
            </div>
            <div style="text-align:right;">
                <p style="margin:0 0 6px; color: rgba(11,11,11,0.55);">Amount</p>
                <h3 style="margin:0; color: var(--phanda-orange);">{{ $booking->formatted_price }}</h3>
            </div>
        </div>

        <form id="mockCheckoutForm" method="POST" action="{{ route('users.payments.simulate-success', $booking->id) }}" style="display:flex;flex-direction:column;gap:14px;">
            @csrf

            <div class="form-group">
                <label for="method">Payment Method</label>
                <select name="method" id="method" class="form-input" required>
                    @foreach($methods as $methodKey => $methodLabel)
                        <option value="{{ $methodKey }}">{{ $methodLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="booking-card__notes" style="margin-top:0;">
                <i class="fas fa-shield-alt"></i> Demo mode only: no real cards or bank accounts are charged.
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:8px;">
                <button type="submit" class="btn-primary btn-sm">
                    <i class="fas fa-check"></i> Simulate Success
                </button>

                <button
                    type="button"
                    class="btn-danger btn-sm"
                    onclick="submitFailure()"
                >
                    <i class="fas fa-times"></i> Simulate Failure
                </button>

                <a href="{{ route('users.bookings') }}" class="btn-outline btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Bookings
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
function submitFailure() {
    const form = document.getElementById('mockCheckoutForm');
    form.action = @json(route('users.payments.simulate-failure', $booking->id));
    form.submit();
}
</script>
@endpush
