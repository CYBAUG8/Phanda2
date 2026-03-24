@extends('Users.layout')

@section('content')
<div class="user-page-shell space-y-6">
    <section class="user-page-header">
        <div>
            <h1>Dashboard</h1>
            <p class="user-page-subtitle">
                Welcome back, {{ $userDisplay }}. Track bookings, messages, and account activity in one place.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('users.services') }}" class="ui-btn-primary">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Find Services</span>
            </a>
            <a href="{{ route('users.bookings') }}" class="ui-btn-secondary">
                <i class="fa-solid fa-calendar-check"></i>
                <span>My Bookings</span>
            </a>
        </div>
    </section>

    @include('partials.ui.flash')

    <section class="user-metrics-grid" aria-label="Customer dashboard metrics">
        @foreach($dashboardStats as $stat)
            @php
                $metricTone = match ($loop->index) {
                    0 => 'text-orange-700',
                    1 => 'text-sky-700',
                    default => 'text-amber-700',
                };
                $iconTone = match ($loop->index) {
                    0 => 'bg-orange-50 text-orange-600',
                    1 => 'bg-sky-50 text-sky-600',
                    default => 'bg-amber-50 text-amber-600',
                };
            @endphp
            <a href="{{ $stat['href'] }}" class="user-metric-card no-underline transition hover:-translate-y-0.5 hover:shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg {{ $iconTone }}">
                        <i class="fas {{ $stat['icon'] }} text-sm"></i>
                    </span>
                    @if(!empty($stat['badge']))
                        <span class="user-status-badge user-status-payment-required">{{ $stat['badge'] }}</span>
                    @endif
                </div>
                <p class="user-metric-label mt-3">{{ $stat['label'] }}</p>
                <p class="user-metric-value {{ $metricTone }}">{{ $stat['value'] }}</p>
            </a>
        @endforeach
    </section>

    <section class="user-section-card">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="user-section-title">Recent Activity</h2>
                <p class="user-section-copy">Latest booking and messaging updates from your account.</p>
            </div>
            <a href="{{ route('users.bookings') }}" class="ui-btn-secondary px-3 py-2 text-xs">
                <i class="fa-solid fa-list-check"></i>
                <span>View bookings</span>
            </a>
        </div>

        @if($activityFeed->isNotEmpty())
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                @foreach($activityFeed as $activity)
                    <a
                        href="{{ $activity['href'] }}"
                        class="flex items-start gap-3 border-b border-slate-100 px-4 py-3 text-slate-900 no-underline transition hover:bg-slate-50 last:border-b-0"
                    >
                        <span class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <i class="fas {{ $activity['icon'] }} text-xs"></i>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold">{{ $activity['text'] }}</span>
                            <span class="block text-xs text-slate-500">{{ $activity['timestamp']->diffForHumans() }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-right mt-1 text-xs text-slate-400" aria-hidden="true"></i>
                    </a>
                @endforeach
            </div>
        @else
            <div class="user-empty-state">
                <h3>No activity yet</h3>
                <p>Your bookings and messages will appear here once you start using the portal.</p>
                <a href="{{ route('users.services') }}" class="ui-btn-primary mt-4">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Find Services</span>
                </a>
            </div>
        @endif
    </section>
</div>
@endsection
