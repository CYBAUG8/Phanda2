@extends('users.layout')

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
            <a href="/users/bookings{{ $key !== 'all' ? '?status=' . $key : '' }}"
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
                            <i class="fas {{ $booking->service->category->icon ?? 'fa-concierge-bell' }}"></i>
                        </div>
                    </div>

                    <div class="booking-card__body">
                        <div class="booking-card__header">
                            <h3 class="booking-card__title">{{ $booking->service->title }}</h3>
                            <span class="status-badge {{ $booking->status_color }}">
                                {{ $booking->status_label }}
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

                        @if($booking->notes)
                            <p class="booking-card__notes">
                                <i class="fas fa-sticky-note"></i> {{ $booking->notes }}
                            </p>
                        @endif
                    </div>

                    <div class="booking-card__right">
                        <span class="booking-card__price">{{ $booking->formatted_price }}</span>

                        <div class="booking-card__actions">
                            @if($booking->can_cancel)
                                <form action="/users/bookings/{{ $booking->id }}/cancel" method="POST"
                                      onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-danger btn-sm">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </form>
                            @endif

                            @if($booking->status === 'completed')
                                <a href="/users/reviews" class="btn-outline btn-sm">
                                    <i class="fas fa-star"></i> Review
                                </a>
                            @endif

                            @if(in_array($booking->status, ['completed', 'cancelled']))
                                <a href="/users/services" class="btn-primary btn-sm">
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
            <a href="{{ $activeStatus === 'all' ? '/users/services' : '/users/bookings' }}" class="btn-primary">
                <i class="fas {{ $activeStatus === 'all' ? 'fa-search' : 'fa-list' }}"></i>
                {{ $activeStatus === 'all' ? 'Find Services' : 'View All Bookings' }}
            </a>
        </div>
    @endif
@endsection