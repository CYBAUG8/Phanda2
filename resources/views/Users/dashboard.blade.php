@extends('Users.layout')

@section('content')
    <div class="page-header dashboard-page-header">
        <h2>Welcome back, {{ $userDisplay }}</h2>
        <p>Track your bookings, messages, and latest account activity in one place.</p>
    </div>

    <div class="stats-row dashboard-stats-row">
        @foreach($dashboardStats as $stat)
            <a href="{{ $stat['href'] }}" class="stat-card card dashboard-stat-card">
                <div class="stat-card__icon dashboard-stat-card__icon {{ $stat['icon_class'] }}">
                    <i class="fas {{ $stat['icon'] }}"></i>
                </div>

                <div class="stat-card__info">
                    <span class="stat-card__number">{{ $stat['value'] }}</span>
                    <span class="stat-card__label">{{ $stat['label'] }}</span>
                </div>

                @if(!empty($stat['badge']))
                    <span class="dashboard-stat-badge">{{ $stat['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <section class="card dashboard-activity-card">
        <div class="dashboard-section-header">
            <h3>Recent Activity</h3>
            <a href="{{ route('users.bookings') }}" class="dashboard-section-link">View bookings</a>
        </div>

        @if($activityFeed->isNotEmpty())
            <div class="dashboard-activity-list">
                @foreach($activityFeed as $activity)
                    <a href="{{ $activity['href'] }}" class="dashboard-activity-item">
                        <span class="dashboard-activity__icon {{ $activity['icon_class'] }}">
                            <i class="fas {{ $activity['icon'] }}"></i>
                        </span>

                        <span class="dashboard-activity__body">
                            <span class="dashboard-activity__title">{{ $activity['text'] }}</span>
                            <span class="dashboard-activity__meta">{{ $activity['timestamp']->diffForHumans() }}</span>
                        </span>

                        <i class="fas fa-chevron-right dashboard-activity__chevron" aria-hidden="true"></i>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state dashboard-empty-state">
                <div class="empty-state__icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3>No activity yet</h3>
                <p>Your bookings and messages will appear here once you start using the portal.</p>
                <a href="{{ route('users.services') }}" class="btn-primary btn-sm">
                    <i class="fas fa-search"></i> Find Services
                </a>
            </div>
        @endif
    </section>
@endsection
