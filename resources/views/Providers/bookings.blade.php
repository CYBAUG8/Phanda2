@extends('Providers.layout')

@section('content')
@php
    $bookingFilters = $bookingFilters ?? [
        'status' => 'pending',
        'q' => '',
        'payment' => 'all',
        'scheduled_for' => 'all',
        'sort' => 'newest',
    ];

    $statusCounts = $statusCounts ?? [
        'all' => 0,
        'pending' => 0,
        'confirmed' => 0,
        'in_progress' => 0,
        'completed' => 0,
        'cancelled' => 0,
    ];

    $bookingMetrics = $bookingMetrics ?? [
        'pending' => 0,
        'confirmed_upcoming' => 0,
        'in_progress' => 0,
        'awaiting_payment' => 0,
    ];

    $statusTabs = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'all' => 'All',
    ];

    $statusBadgeClasses = [
        'pending' => 'provider-status-pending',
        'confirmed' => 'provider-status-confirmed',
        'in_progress' => 'provider-status-in-progress',
        'completed' => 'provider-status-completed',
        'cancelled' => 'provider-status-cancelled',
    ];

    $paymentBadgeClasses = [
        \App\Models\Booking::PAYMENT_STATUS_REQUIRED => 'provider-status-payment-required',
        \App\Models\Booking::PAYMENT_STATUS_PAID => 'provider-status-payment-paid',
        \App\Models\Booking::PAYMENT_STATUS_FAILED => 'provider-status-payment-failed',
        \App\Models\Booking::PAYMENT_STATUS_REFUNDED => 'provider-status-payment-refunded',
        \App\Models\Booking::PAYMENT_STATUS_UNPAID => 'provider-status-payment-unpaid',
    ];

    $sortOptions = [
        'newest' => 'Newest',
        'oldest' => 'Oldest',
        'scheduled_asc' => 'Soonest first',
        'scheduled_desc' => 'Latest scheduled',
        'amount_high' => 'Amount: High to Low',
        'amount_low' => 'Amount: Low to High',
    ];

    $activeStatus = $bookingFilters['status'] ?? 'pending';
    $hasFilters = trim((string) ($bookingFilters['q'] ?? '')) !== ''
        || (($bookingFilters['payment'] ?? 'all') !== 'all')
        || (($bookingFilters['scheduled_for'] ?? 'all') !== 'all')
        || (($bookingFilters['sort'] ?? 'newest') !== 'newest');

    $emptyCopy = [
        'pending' => 'No pending booking requests right now.',
        'confirmed' => 'No confirmed bookings yet.',
        'in_progress' => 'No bookings are currently in progress.',
        'completed' => 'No completed bookings yet.',
        'cancelled' => 'No cancelled bookings yet.',
        'all' => 'No bookings found.',
    ];
@endphp

<div x-data="providerBookingsPage()" class="mx-auto max-w-6xl space-y-6">
    <section class="provider-page-header">
        <div>
            <h1>Bookings</h1>
            <p class="provider-page-subtitle">Track requests, update statuses, and keep customers informed in real time.</p>
        </div>
    </section>

    <section class="provider-metrics-grid" aria-label="Booking summary metrics">
        <article class="provider-metric-card">
            <p class="provider-metric-label">Pending</p>
            <p class="provider-metric-value text-amber-700">{{ number_format((int) $bookingMetrics['pending']) }}</p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Upcoming Confirmed</p>
            <p class="provider-metric-value text-blue-700">{{ number_format((int) $bookingMetrics['confirmed_upcoming']) }}</p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">In Progress</p>
            <p class="provider-metric-value text-indigo-700">{{ number_format((int) $bookingMetrics['in_progress']) }}</p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Awaiting Payment</p>
            <p class="provider-metric-value text-orange-700">{{ number_format((int) $bookingMetrics['awaiting_payment']) }}</p>
        </article>
    </section>

    @include('partials.ui.flash')

    <section class="provider-filter-bar space-y-3">
        <div class="flex gap-2 overflow-x-auto whitespace-nowrap pb-1">
            @foreach($statusTabs as $statusKey => $label)
                @php
                    $tabQuery = array_merge($bookingFilters, ['status' => $statusKey]);
                @endphp
                <a
                    href="{{ route('providers.bookings', $tabQuery) }}"
                    class="provider-segmented-link border border-slate-200 {{ $activeStatus === $statusKey ? 'is-active' : '' }}"
                >
                    <span>{{ $label }}</span>
                    <span class="provider-status-badge provider-status-paused">
                        {{ number_format((int) ($statusCounts[$statusKey] ?? 0)) }}
                    </span>
                </a>
            @endforeach
        </div>

        <form action="{{ route('providers.bookings') }}" method="GET" class="provider-filter-grid">
            <input type="hidden" name="status" value="{{ $activeStatus }}">

            <div>
                <label for="bookingSearch" class="provider-label">Search</label>
                <input
                    id="bookingSearch"
                    type="text"
                    name="q"
                    value="{{ $bookingFilters['q'] }}"
                    class="provider-input"
                    placeholder="Search by code, customer, service, or address"
                >
            </div>

            <div>
                <label for="paymentFilter" class="provider-label">Payment</label>
                <select id="paymentFilter" name="payment" class="provider-select">
                    <option value="all" @selected(($bookingFilters['payment'] ?? 'all') === 'all')>All payment states</option>
                    <option value="required" @selected(($bookingFilters['payment'] ?? 'all') === 'required')>Awaiting Payment</option>
                    <option value="paid" @selected(($bookingFilters['payment'] ?? 'all') === 'paid')>Paid</option>
                    <option value="failed" @selected(($bookingFilters['payment'] ?? 'all') === 'failed')>Failed</option>
                    <option value="refunded" @selected(($bookingFilters['payment'] ?? 'all') === 'refunded')>Refunded</option>
                    <option value="unpaid" @selected(($bookingFilters['payment'] ?? 'all') === 'unpaid')>Unpaid</option>
                </select>
            </div>

            <div>
                <label for="scheduledForFilter" class="provider-label">Scheduled</label>
                <select id="scheduledForFilter" name="scheduled_for" class="provider-select">
                    <option value="all" @selected(($bookingFilters['scheduled_for'] ?? 'all') === 'all')>All dates</option>
                    <option value="today" @selected(($bookingFilters['scheduled_for'] ?? 'all') === 'today')>Today</option>
                    <option value="upcoming" @selected(($bookingFilters['scheduled_for'] ?? 'all') === 'upcoming')>Upcoming</option>
                    <option value="past" @selected(($bookingFilters['scheduled_for'] ?? 'all') === 'past')>Past</option>
                </select>
            </div>

            <div>
                <label for="bookingSort" class="provider-label">Sort</label>
                <select id="bookingSort" name="sort" class="provider-select">
                    @foreach($sortOptions as $key => $label)
                        <option value="{{ $key }}" @selected(($bookingFilters['sort'] ?? 'newest') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="provider-filter-actions">
                <button type="submit" class="ui-btn-primary w-full justify-center">Apply</button>
                <a href="{{ route('providers.bookings', ['status' => $activeStatus]) }}" class="ui-btn-secondary w-full justify-center">Reset</a>
            </div>
        </form>
    </section>

    @if($bookings->total() === 0)
        <section class="provider-empty-state">
            <h3>{{ $emptyCopy[$activeStatus] ?? $emptyCopy['all'] }}</h3>
            <p>
                {{ $hasFilters ? 'Try clearing filters or searching with broader terms.' : 'New bookings will appear here as soon as customers send requests.' }}
            </p>
            @if($hasFilters)
                <div class="mt-4">
                    <a href="{{ route('provider.bookings.index', ['status' => $activeStatus]) }}" class="ui-btn-secondary">Clear Filters</a>
                </div>
            @endif
        </section>
    @else
        <section class="space-y-4">
            @foreach($bookings as $booking)
                @php
                    $statusClass = $booking->isExpired()
                        ? 'provider-status-paused'
                        : ($statusBadgeClasses[$booking->status] ?? 'provider-status-paused');
                    $paymentClass = $paymentBadgeClasses[$booking->payment_status] ?? 'provider-status-payment-unpaid';
                @endphp
                <article class="ui-card p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $booking->booking_code }}</p>
                            <h2 class="text-lg font-semibold text-slate-900">{{ $booking->service->title ?? 'Service' }}</h2>
                            <p class="text-sm text-slate-600">
                                <i class="fa-solid fa-user mr-1 text-slate-400"></i>
                                {{ $booking->user->full_name ?? 'Unknown Customer' }}
                            </p>
                            <p class="text-sm text-slate-600">
                                <i class="fa-regular fa-calendar mr-1 text-slate-400"></i>
                                {{ optional($booking->booking_date)->format('d M Y') ?? 'Date not set' }}
                                @if($booking->start_time)
                                    at {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}
                                @endif
                            </p>

                            <div class="flex flex-wrap items-center gap-2">
                                <span class="provider-status-badge {{ $statusClass }}">{{ $booking->status_label }}</span>
                                <span class="provider-status-badge {{ $paymentClass }}">{{ $booking->payment_status_label }}</span>
                                <span class="text-sm font-semibold text-slate-900">R{{ number_format((float) $booking->total_price, 2) }}</span>
                            </div>

                            @if($booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_REQUIRED)
                                <p class="rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 text-xs text-orange-800">
                                    Payment is required before this booking can start.
                                </p>
                            @endif

                            @if($booking->isExpired())
                                <p class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
                                    This booking expired because the scheduled end time passed before completion.
                                </p>
                            @endif
                        </div>

                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[220px]">
                            <button
                                type="button"
                                @click="openDetails({
                                    code: @js($booking->booking_code),
                                    service: @js($booking->service->title ?? 'Service'),
                                    customer: @js($booking->user->full_name ?? 'Unknown Customer'),
                                    date: @js(optional($booking->booking_date)->format('d M Y') ?? 'N/A'),
                                    time: @js($booking->start_time ? \Carbon\Carbon::parse($booking->start_time)->format('H:i') : 'N/A'),
                                    status: @js($booking->status_label),
                                    payment: @js($booking->payment_status_label),
                                    address: @js($booking->address ?: 'No address provided'),
                                    notes: @js($booking->notes ?: 'No notes provided'),
                                    amount: @js('R' . number_format((float) $booking->total_price, 2)),
                                    message_url: @js(($booking->user && $booking->user->user_id) ? url('/providers/messages/start/' . $booking->user->user_id) : '')
                                })"
                                class="ui-btn-secondary min-h-11 justify-center"
                            >
                                View Details
                            </button>

                            @if($booking->status === \App\Models\Booking::STATUS_PENDING)
                                <form method="POST" action="{{ route('provider.bookings.confirm', $booking->id) }}" data-submit-lock="true">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="ui-btn-primary min-h-11 w-full justify-center" data-loading-text="Accepting...">Accept</button>
                                </form>

                                <form
                                    method="POST"
                                    action="{{ route('provider.bookings.cancel', $booking->id) }}"
                                    data-confirm="Decline this booking request?"
                                    data-confirm-title="Decline booking"
                                    data-confirm-text="Decline"
                                    data-confirm-variant="danger"
                                    data-submit-lock="true"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="ui-btn-danger min-h-11 w-full justify-center" data-loading-text="Declining...">Decline</button>
                                </form>
                            @elseif($booking->status === \App\Models\Booking::STATUS_CONFIRMED)
                                @if($booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_PAID)
                                    <form method="POST" action="{{ route('provider.bookings.start', $booking->id) }}" data-submit-lock="true">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="ui-btn-primary min-h-11 w-full justify-center" data-loading-text="Starting...">Start</button>
                                    </form>
                                @else
                                    <span class="provider-status-badge provider-status-payment-required justify-center px-3 py-2">
                                        Awaiting User Payment
                                    </span>
                                @endif

                                <form
                                    method="POST"
                                    action="{{ route('provider.bookings.cancel', $booking->id) }}"
                                    data-confirm="Cancel this confirmed booking?"
                                    data-confirm-title="Cancel booking"
                                    data-confirm-text="Cancel booking"
                                    data-confirm-variant="danger"
                                    data-submit-lock="true"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="ui-btn-danger min-h-11 w-full justify-center" data-loading-text="Cancelling...">Cancel Booking</button>
                                </form>
                            @elseif($booking->status === \App\Models\Booking::STATUS_IN_PROGRESS)
                                <form method="POST" action="{{ route('provider.bookings.complete', $booking->id) }}" data-submit-lock="true">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="ui-btn-primary min-h-11 w-full justify-center" data-loading-text="Completing...">Mark Completed</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        @if($bookings->hasPages())
            <section class="pt-2">
                {{ $bookings->onEachSide(1)->links() }}
            </section>
        @endif
    @endif

    <div
        x-show="detailsModalOpen"
        x-cloak
        x-on:keydown.escape.window="detailsModalOpen = false"
        class="fixed inset-0 z-50 overflow-y-auto p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="bookingDetailsTitle"
    >
        <div class="flex min-h-full items-center justify-center">
            <div class="fixed inset-0 bg-slate-950/50" @click="detailsModalOpen = false"></div>

            <div class="provider-modal-panel relative z-10 w-full max-w-2xl">
                <div class="provider-modal-header">
                    <h3 id="bookingDetailsTitle" class="text-lg font-semibold text-slate-900">Booking Details</h3>
                    <button type="button" class="text-slate-500 hover:text-slate-700" @click="detailsModalOpen = false" aria-label="Close booking details modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="provider-modal-body">
                    <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="provider-label normal-case tracking-normal text-slate-500">Booking Code</dt>
                            <dd class="font-semibold text-slate-900" x-text="selectedBooking.code"></dd>
                        </div>
                        <div>
                            <dt class="provider-label normal-case tracking-normal text-slate-500">Amount</dt>
                            <dd class="font-semibold text-slate-900" x-text="selectedBooking.amount"></dd>
                        </div>
                        <div>
                            <dt class="provider-label normal-case tracking-normal text-slate-500">Service</dt>
                            <dd class="font-semibold text-slate-900" x-text="selectedBooking.service"></dd>
                        </div>
                        <div>
                            <dt class="provider-label normal-case tracking-normal text-slate-500">Customer</dt>
                            <dd class="font-semibold text-slate-900" x-text="selectedBooking.customer"></dd>
                        </div>
                        <div>
                            <dt class="provider-label normal-case tracking-normal text-slate-500">Date</dt>
                            <dd class="font-semibold text-slate-900" x-text="selectedBooking.date"></dd>
                        </div>
                        <div>
                            <dt class="provider-label normal-case tracking-normal text-slate-500">Time</dt>
                            <dd class="font-semibold text-slate-900" x-text="selectedBooking.time"></dd>
                        </div>
                        <div>
                            <dt class="provider-label normal-case tracking-normal text-slate-500">Status</dt>
                            <dd class="font-semibold text-slate-900" x-text="selectedBooking.status"></dd>
                        </div>
                        <div>
                            <dt class="provider-label normal-case tracking-normal text-slate-500">Payment</dt>
                            <dd class="font-semibold text-slate-900" x-text="selectedBooking.payment"></dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="provider-label normal-case tracking-normal text-slate-500">Address</dt>
                            <dd class="font-semibold text-slate-900" x-text="selectedBooking.address"></dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="provider-label normal-case tracking-normal text-slate-500">Notes</dt>
                            <dd class="font-semibold text-slate-900" x-text="selectedBooking.notes"></dd>
                        </div>
                    </dl>
                </div>

                <div class="provider-modal-footer">
                    <a
                        x-show="selectedBooking.message_url"
                        :href="selectedBooking.message_url"
                        class="ui-btn-secondary"
                    >
                        <i class="fa-solid fa-comment"></i>
                        Message Customer
                    </a>
                    <button type="button" @click="detailsModalOpen = false" class="ui-btn-primary">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function providerBookingsPage() {
    return {
        detailsModalOpen: false,
        selectedBooking: {
            code: '',
            service: '',
            customer: '',
            date: '',
            time: '',
            status: '',
            payment: '',
            address: '',
            notes: '',
            amount: '',
            message_url: '',
        },
        openDetails(booking) {
            this.selectedBooking = { ...booking };
            this.detailsModalOpen = true;
        },
    };
}

(() => {
    if (window.__providerSubmitLockInit) {
        return;
    }
    window.__providerSubmitLockInit = true;

    document.addEventListener('submit', (event) => {
        if (event.defaultPrevented) {
            return;
        }

        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.dataset.submitLock !== 'true') {
            return;
        }

        const submitter = event.submitter;
        if (!(submitter instanceof HTMLButtonElement)) {
            return;
        }

        if (submitter.disabled) {
            event.preventDefault();
            return;
        }

        submitter.disabled = true;
        const loadingText = submitter.dataset.loadingText;
        if (loadingText) {
            submitter.dataset.originalText = submitter.innerHTML;
            submitter.innerHTML = loadingText;
        }
    });
})();
</script>
@endpush
@endsection