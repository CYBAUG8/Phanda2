@extends('Providers.layout')

@section('content')
<div x-data="providerDashboardPage()" class="provider-page-shell space-y-6">
    <section class="provider-page-header">
        <div>
            <h1>Dashboard</h1>
            <p class="provider-page-subtitle">Monitor bookings, earnings, and your provider availability from one place.</p>
        </div>

        <div class="ui-card w-full max-w-sm p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Availability</p>
            <div class="mt-2 flex items-center justify-between gap-3">
                <div>
                    <p class="provider-status-dot" :class="active ? 'text-emerald-700' : 'text-slate-500'" x-text="active ? 'Online' : 'Offline'"></p>
                    <p class="mt-1 text-xs text-slate-500" x-text="active ? 'Customers can send new requests.' : 'New requests are temporarily paused.'"></p>
                </div>
                <button
                    type="button"
                    class="relative inline-flex h-7 w-12 items-center rounded-full border border-transparent transition focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                    :class="active ? 'bg-emerald-500' : 'bg-slate-300'"
                    :aria-checked="active ? 'true' : 'false'"
                    :aria-busy="pending ? 'true' : 'false'"
                    role="switch"
                    @click="toggle()"
                    :disabled="pending"
                >
                    <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow-sm transition" :class="active ? 'translate-x-6' : 'translate-x-1'"></span>
                    <span class="sr-only">Toggle provider availability</span>
                </button>
            </div>
        </div>
    </section>

    @include('partials.ui.flash')

    <section class="provider-metrics-grid" aria-label="Provider dashboard metrics">
        <article class="provider-metric-card">
            <p class="provider-metric-label">Average Rating</p>
            <p class="provider-metric-value text-amber-600">{{ number_format((float) ($averageRating ?? 0), 1) }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ number_format((int) ($totalReviews ?? 0)) }} reviews</p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Total Bookings</p>
            <p class="provider-metric-value">{{ number_format((int) ($totalBookings ?? 0)) }}</p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Completed</p>
            <p class="provider-metric-value text-emerald-700">{{ number_format((int) ($completedBookings ?? 0)) }}</p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Pending</p>
            <p class="provider-metric-value text-amber-700">{{ number_format((int) ($pendingBookings ?? 0)) }}</p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Available Balance</p>
            <p class="provider-metric-value text-orange-700">R{{ number_format((float) ($availableBalance ?? 0), 2) }}</p>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <article class="provider-section-card">
            <h2 class="provider-section-title">Bookings</h2>
            <p class="provider-section-copy">Review and action customer requests.</p>
            <div class="mt-4">
                <a href="{{ route('provider.bookings.index') }}" class="ui-btn-secondary">Manage Bookings</a>
            </div>
        </article>
        <article class="provider-section-card">
            <h2 class="provider-section-title">Services</h2>
            <p class="provider-section-copy">Update pricing, details, and availability.</p>
            <div class="mt-4">
                <a href="{{ route('provider.services.index') }}" class="ui-btn-secondary">Manage Services</a>
            </div>
        </article>
        <article class="provider-section-card">
            <h2 class="provider-section-title">Earnings</h2>
            <p class="provider-section-copy">Track payouts and available funds.</p>
            <div class="mt-4">
                <a href="{{ route('provider.earnings') }}" class="ui-btn-secondary">View Earnings</a>
            </div>
        </article>
    </section>

    <section class="ui-card overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="provider-section-title">Recent Bookings</h2>
                <p class="provider-section-copy">Latest activity from your customer requests.</p>
            </div>
            <a href="{{ route('provider.bookings.index') }}" class="ui-btn-secondary">View All</a>
        </div>

        @if(($recentBookings ?? collect())->isNotEmpty())
            <div class="overflow-x-auto px-5 py-4">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="py-3 pr-3">Booking</th>
                            <th class="py-3 pr-3">Service</th>
                            <th class="py-3 pr-3">Status</th>
                            <th class="py-3 pr-3">Amount</th>
                            <th class="py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach(($recentBookings ?? collect()) as $booking)
                            @php
                                $statusClass = match ($booking->status) {
                                    'completed' => 'provider-status-completed',
                                    'pending' => 'provider-status-pending',
                                    'confirmed' => 'provider-status-confirmed',
                                    'in_progress' => 'provider-status-in-progress',
                                    'cancelled' => 'provider-status-cancelled',
                                    default => 'provider-status-paused',
                                };
                            @endphp
                            <tr class="align-top">
                                <td class="py-3 pr-3 font-medium text-slate-700">#{{ $booking->id }}</td>
                                <td class="py-3 pr-3 text-slate-800">{{ $booking->service->title ?? 'Service' }}</td>
                                <td class="py-3 pr-3">
                                    <span class="provider-status-badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                                </td>
                                <td class="py-3 pr-3 font-semibold text-slate-900">R{{ number_format((float) $booking->total_price, 2) }}</td>
                                <td class="py-3 text-slate-600">{{ optional($booking->created_at)->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="provider-empty-state border-0 border-t border-slate-200 rounded-none">
                <h3>No bookings yet</h3>
                <p>New customer requests will appear here once your services are live.</p>
                <div class="mt-4">
                    <a href="{{ route('provider.services.index') }}" class="ui-btn-secondary">Review Services</a>
                </div>
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
function providerDashboardPage() {
    return {
        active: @json((bool) ($isOnline ?? false)),
        pending: false,
        async toggle() {
            if (this.pending) {
                return;
            }

            const previous = this.active;
            const next = !this.active;

            this.active = next;
            this.pending = true;

            try {
                const response = await fetch('{{ route('provider.toggleOnline') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ is_online: next }),
                });

                const payload = await response.json();
                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Unable to update your availability.');
                }

                this.active = Boolean(payload.is_online);
                window.uiToast(this.active ? 'You are now online.' : 'You are now offline.', 'success');
            } catch (error) {
                this.active = previous;
                window.uiToast(error.message || 'Unable to update availability right now.', 'error');
            } finally {
                this.pending = false;
            }
        },
    };
}
</script>
@endpush
