@extends('Users.layout')

@section('content')
<div class="user-page-shell space-y-6">
    <section class="user-page-header">
        <div>
            <h1>My Bookings</h1>
            <p class="user-page-subtitle">Track service progress, payments, and next actions for every booking.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('users.services') }}" class="ui-btn-primary">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Find Services</span>
            </a>
            <a href="{{ route('reviews.reviews') }}" class="ui-btn-secondary">
                <i class="fa-solid fa-star"></i>
                <span>Reviews</span>
            </a>
        </div>
    </section>

    @include('partials.ui.flash')

    <section class="user-metrics-grid" aria-label="Booking metrics">
        <article class="user-metric-card">
            <p class="user-metric-label">Total Bookings</p>
            <p class="user-metric-value text-slate-900">{{ $stats['total'] }}</p>
        </article>
        <article class="user-metric-card">
            <p class="user-metric-label">Upcoming</p>
            <p class="user-metric-value text-sky-700">{{ $stats['upcoming'] }}</p>
        </article>
        <article class="user-metric-card">
            <p class="user-metric-label">Completed</p>
            <p class="user-metric-value text-emerald-700">{{ $stats['completed'] }}</p>
        </article>
        <article class="user-metric-card">
            <p class="user-metric-label">Needing Attention</p>
            <p class="user-metric-value text-orange-700">
                {{ $bookings->whereIn('payment_status', [\App\Models\Booking::PAYMENT_STATUS_REQUIRED, \App\Models\Booking::PAYMENT_STATUS_FAILED])->count() }}
            </p>
        </article>
    </section>

    @php
        $tabs = [
            'all' => ['label' => 'All', 'icon' => 'fa-list'],
            'pending' => ['label' => 'Pending', 'icon' => 'fa-clock'],
            'confirmed' => ['label' => 'Confirmed', 'icon' => 'fa-check'],
            'in_progress' => ['label' => 'In Progress', 'icon' => 'fa-spinner'],
            'completed' => ['label' => 'Completed', 'icon' => 'fa-circle-check'],
            'cancelled' => ['label' => 'Cancelled', 'icon' => 'fa-ban'],
        ];
    @endphp

    <section class="user-section-card space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="user-section-title">Booking List</h2>
                <p class="user-section-copy">Use filters to focus on specific booking states.</p>
            </div>
        </div>

        <div class="user-segmented overflow-x-auto" role="tablist" aria-label="Booking status filters">
            @foreach($tabs as $key => $tab)
                <a
                    href="{{ route('users.bookings', $key !== 'all' ? ['status' => $key] : []) }}"
                    class="user-segmented-link {{ $activeStatus === $key ? 'is-active' : '' }}"
                    role="tab"
                    aria-selected="{{ $activeStatus === $key ? 'true' : 'false' }}"
                >
                    <i class="fa-solid {{ $tab['icon'] }}"></i>
                    <span>{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </div>

        @if($bookings->isNotEmpty())
            <div class="space-y-3">
                @foreach($bookings as $booking)
                    @php
                        $statusClass = match ($booking->display_status) {
                            'pending' => 'user-status-pending',
                            'confirmed' => 'user-status-confirmed',
                            'in_progress' => 'user-status-in-progress',
                            'completed' => 'user-status-completed',
                            'cancelled' => 'user-status-cancelled',
                            'expired' => 'user-status-expired',
                            default => 'user-status-pending',
                        };

                        $paymentClass = match ($booking->payment_status) {
                            \App\Models\Booking::PAYMENT_STATUS_REQUIRED => 'user-status-payment-required',
                            \App\Models\Booking::PAYMENT_STATUS_PAID => 'user-status-payment-paid',
                            \App\Models\Booking::PAYMENT_STATUS_REFUNDED => 'user-status-payment-refunded',
                            \App\Models\Booking::PAYMENT_STATUS_FAILED => 'user-status-payment-failed',
                            default => 'user-status-payment-unpaid',
                        };
                    @endphp

                    <article class="ui-card p-4 sm:p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 space-y-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-semibold text-slate-900">
                                        {{ $booking->service->title ?? 'Service Booking' }}
                                    </h3>
                                    <span class="user-status-badge {{ $statusClass }}">{{ $booking->status_label }}</span>
                                    <span class="user-status-badge {{ $paymentClass }}">{{ $booking->payment_status_label }}</span>
                                </div>

                                <p class="text-sm text-slate-600">
                                    <i class="fa-solid fa-user mr-1 text-slate-400"></i>
                                    {{ $booking->service->provider_name ?? 'Provider' }}
                                </p>

                                <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm text-slate-600">
                                    <span>
                                        <i class="fa-regular fa-calendar mr-1 text-slate-400"></i>
                                        {{ $booking->booking_date->format('d M Y') }}
                                    </span>
                                    <span>
                                        <i class="fa-regular fa-clock mr-1 text-slate-400"></i>
                                        {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}
                                    </span>
                                    <span>
                                        <i class="fa-solid fa-location-dot mr-1 text-slate-400"></i>
                                        {{ $booking->address }}
                                    </span>
                                </div>

                                @if($booking->payment_due_at && $booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_REQUIRED)
                                    <p class="rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 text-sm text-orange-800">
                                        <i class="fa-solid fa-hourglass-half mr-1"></i>
                                        Payment due by {{ $booking->payment_due_at->timezone('Africa/Johannesburg')->format('d M Y H:i') }}.
                                    </p>
                                @endif

                                @if($booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_FAILED)
                                    <p class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                        Payment failed. Retry to keep this booking active.
                                    </p>
                                @endif

                                @if($booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_REFUNDED)
                                    <p class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-sky-800">
                                        <i class="fa-solid fa-rotate-left mr-1"></i>
                                        Full refund processed.
                                    </p>
                                @endif

                                @if($booking->notes)
                                    <p class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                        <i class="fa-solid fa-note-sticky mr-1"></i>
                                        {{ $booking->notes }}
                                    </p>
                                @endif

                                @if($booking->isExpired())
                                    <p class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-700">
                                        <i class="fa-solid fa-hourglass-end mr-1"></i>
                                        This booking expired because its scheduled time passed before completion.
                                    </p>
                                @endif
                            </div>

                            <div class="flex w-full flex-col gap-2 lg:w-auto lg:min-w-[210px]">
                                <p class="text-lg font-bold text-slate-900">{{ $booking->formatted_price }}</p>

                                @if($booking->status === \App\Models\Booking::STATUS_CONFIRMED && in_array($booking->payment_status, [\App\Models\Booking::PAYMENT_STATUS_REQUIRED, \App\Models\Booking::PAYMENT_STATUS_FAILED, \App\Models\Booking::PAYMENT_STATUS_UNPAID], true))
                                    <form action="{{ route('users.payments.initiate', $booking->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="ui-btn-primary min-h-11 w-full justify-center">
                                            <i class="fa-solid fa-credit-card"></i>
                                            <span>Pay Now</span>
                                        </button>
                                    </form>
                                @endif

                                @if($booking->can_cancel)
                                    <form
                                        action="{{ route('users.bookings.cancel', $booking->id) }}"
                                        method="POST"
                                        data-confirm="Are you sure you want to cancel this booking?"
                                        data-confirm-title="Cancel booking"
                                        data-confirm-text="Cancel booking"
                                        data-confirm-variant="danger"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="ui-btn-danger min-h-11 w-full justify-center">
                                            <i class="fa-solid fa-xmark"></i>
                                            <span>Cancel</span>
                                        </button>
                                    </form>
                                @endif

                                @if($booking->status === \App\Models\Booking::STATUS_COMPLETED)
                                    <a href="{{ route('reviews.reviews', ['booking' => $booking->id]) }}" class="ui-btn-secondary min-h-11 justify-center">
                                        <i class="fa-solid fa-star"></i>
                                        <span>Review</span>
                                    </a>
                                @endif

                                @if($booking->status === \App\Models\Booking::STATUS_COMPLETED || $booking->isManuallyCancelled())
                                    <a href="{{ route('users.services') }}" class="ui-btn-secondary min-h-11 justify-center">
                                        <i class="fa-solid fa-rotate-right"></i>
                                        <span>Rebook</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="user-empty-state">
                <h3>No bookings {{ $activeStatus !== 'all' ? 'with status "' . str_replace('_', ' ', $activeStatus) . '"' : 'yet' }}</h3>
                <p>
                    @if($activeStatus === 'all')
                        Find a service to get started with your first booking.
                    @else
                        Try viewing all bookings or switching to a different status filter.
                    @endif
                </p>
                <a href="{{ $activeStatus === 'all' ? route('users.services') : route('users.bookings') }}" class="ui-btn-primary mt-4">
                    <i class="fa-solid {{ $activeStatus === 'all' ? 'fa-magnifying-glass' : 'fa-list' }}"></i>
                    <span>{{ $activeStatus === 'all' ? 'Find Services' : 'View All Bookings' }}</span>
                </a>
            </div>
        @endif
    </section>
</div>
@endsection
