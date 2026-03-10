@extends('Users.layout')

@section('content')
    <div class="page-header">
        <h2>My Bookings</h2>
        <p>Track and manage all your service bookings.</p>
    </div>

    {{-- Stats Row --}}
    <div class="stats-row">
        <div class="stat-card card">
            <div class="stat-card__icon stat-card__icon--total">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-card__info">
                <span class="stat-card__number">{{ $stats['total'] }}</span>
                <span class="stat-card__label">Total Bookings</span>
            </div>
        </div>
        <div class="stat-card card">
            <div class="stat-card__icon stat-card__icon--upcoming">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-card__info">
                <span class="stat-card__number">{{ $stats['upcoming'] }}</span>
                <span class="stat-card__label">Upcoming</span>
            </div>
        </div>
        <div class="stat-card card">
            <div class="stat-card__icon stat-card__icon--completed">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-card__info">
                <span class="stat-card__number">{{ $stats['completed'] }}</span>
                <span class="stat-card__label">Completed</span>
            </div>
        </div>
    </div>

    {{-- Status Tabs --}}
    <div class="status-tabs">
        @php
            $tabs = [
                'all'         => ['label' => 'All',         'icon' => 'fa-list'],
                'pending'     => ['label' => 'Pending',     'icon' => 'fa-clock'],
                'confirmed'   => ['label' => 'Confirmed',   'icon' => 'fa-check'],
                'in_progress' => ['label' => 'In Progress', 'icon' => 'fa-spinner'],
                'completed'   => ['label' => 'Completed',   'icon' => 'fa-check-circle'],
                'cancelled'   => ['label' => 'Cancelled',   'icon' => 'fa-times-circle'],
            ];
        @endphp
        @foreach($tabs as $key => $tab)
            <a href="{{ route('users.bookings', $key !== 'all' ? ['status' => $key] : []) }}"
               class="status-tab {{ $activeStatus === $key ? 'status-tab--active' : '' }}">
                <i class="fas {{ $tab['icon'] }}"></i>
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Booking Cards --}}
    @if($bookings->count() > 0)
        <div class="bookings-list">
            @foreach($bookings as $booking)
                <div class="booking-card card">
                    <div class="booking-card__left">
                        <div class="booking-card__icon">
                            <i class="fas {{ optional($booking->service->category)->icon ?? 'fa-concierge-bell' }}"></i>
                        </div>
                    </div>

                    <div class="booking-card__body">
                        <div class="booking-card__header">
                            <h3 class="booking-card__title">{{ $booking->service->title }}</h3>
                            <span class="status-badge {{ $booking->status_color }}">
                                {{ $booking->status_label }}
                            </span>
                            <span class="status-badge payment-badge {{ $booking->payment_status_color }}">
                                {{ $booking->payment_status_label }}
                            </span>
                        </div>

                        <p class="booking-card__provider">
                            <i class="fas fa-user-circle"></i> {{ $booking->service->provider_name }}
                        </p>

                        <div class="booking-card__details">
                            <span>
                                <i class="far fa-calendar"></i>
                                {{ $booking->booking_date->format('d M Y') }}
                            </span>
                            <span>
                                <i class="far fa-clock"></i>
                                {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}
                            </span>
                            <span>
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $booking->address }}
                            </span>
                        </div>

                        @if($booking->payment_due_at && $booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_REQUIRED)
                            <p class="booking-card__notes">
                                <i class="fas fa-hourglass-half"></i>
                                Payment due by {{ $booking->payment_due_at->timezone('Africa/Johannesburg')->format('d M Y H:i') }}.
                            </p>
                        @endif

                        @if($booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_FAILED)
                            <p class="booking-card__notes">
                                <i class="fas fa-triangle-exclamation"></i> Payment failed. Retry to keep this booking active.
                            </p>
                        @endif

                        @if($booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_REFUNDED)
                            <p class="booking-card__notes">
                                <i class="fas fa-rotate-left"></i> Full refund processed.
                            </p>
                        @endif

                        @if($booking->notes)
                            <p class="booking-card__notes">
                                <i class="fas fa-sticky-note"></i> {{ $booking->notes }}
                            </p>
                        @endif

                        @if($booking->isExpired())
                            <p class="booking-card__notes">
                                <i class="fas fa-hourglass-end"></i> This booking expired because its scheduled time passed before it was completed.
                            </p>
                        @endif
                    </div>

                    <div class="booking-card__right">
                        <span class="booking-card__price">{{ $booking->formatted_price }}</span>

                        <div class="booking-card__actions">
                            @if($booking->status === \App\Models\Booking::STATUS_CONFIRMED && in_array($booking->payment_status, [\App\Models\Booking::PAYMENT_STATUS_REQUIRED, \App\Models\Booking::PAYMENT_STATUS_FAILED, \App\Models\Booking::PAYMENT_STATUS_UNPAID], true))
                                <form action="{{ route('users.payments.initiate', $booking->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-primary btn-sm">
                                        <i class="fas fa-credit-card"></i> Pay Now
                                    </button>
                                </form>
                            @endif

                            @if($booking->can_cancel)
                                <form action="{{ route('users.bookings.cancel', $booking->id) }}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-danger btn-sm">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </form>
                            @endif

                            @if($booking->status === 'completed')
                                <a href="{{ route('reviews.reviews', ['booking' => $booking->id]) }}" class="btn-outline btn-sm">
                                    <i class="fas fa-star"></i> Review
                                </a>
                            @endif

                            @if($booking->status === 'completed' || $booking->isManuallyCancelled())
                                <a href="{{ route('users.services') }}" class="btn-primary btn-sm">
                                    <i class="fas fa-redo"></i> Rebook
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="empty-state card">
            <div class="empty-state__icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h3>No bookings {{ $activeStatus !== 'all' ? 'with status "' . str_replace('_', ' ', $activeStatus) . '"' : 'yet' }}</h3>
            <p>
                @if($activeStatus === 'all')
                    Find a service to get started with your first booking!
                @else
                    Try viewing all bookings or a different status filter.
                @endif
            </p>
            <a href="{{ $activeStatus === 'all' ? route('users.services') : route('users.bookings') }}" class="btn-primary">
                <i class="fas {{ $activeStatus === 'all' ? 'fa-search' : 'fa-list' }}"></i>
                {{ $activeStatus === 'all' ? 'Find Services' : 'View All Bookings' }}
            </a>
        </div>
    @endif
@endsection
