@extends('users.layout')

@section('content')

<div class="container">
<!-- Header -->
<section>
    <div class="bg-white border rounded-lg p-8 shadow-sm mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Welcome back, {{ $user->full_name ?? 'User' }} </h2>
        <p class="text-muted">Here’s what’s happening with your account today.</p>
    </div>
</section>


{{-- Stats Grid --}}
<div class="stats-grid mb-4">
    <a href="{{ url('/users/bookings') }}" class="stat-card card">
        <div class="stat-label">Active Bookings</div>
        <div class="stat-value">{{ $totalBookings ?? 0 }}</div>
    </a>

    <a href="{{ url('/users/messages') }}" class="stat-card card">
        <div class="stat-label">Messages</div>
        <div class="stat-value">{{ $unreadMessages ?? 0 }}</div>

        @if(($unreadMessages ?? 0) > 0)
            <div class="chip chip-attn">
                {{ $unreadMessages }} new
            </div>
        @endif
    </a>
</div>

{{-- Recent Activity --}}
<section class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Recent Activity</div>
            <div class="card-subtitle">
                Your latest bookings, messages, and payments
            </div>
        </div>
    </div>

    @if(!empty($activities))
        <div class="list">
            @foreach($activities as $a)
                <div class="list-row">
                    
                    <div class="activity-icon activity-{{ $a['type'] }}">
                        @switch($a['type'])
                            @case('booking') 📅 @break
                            @case('message') 💬 @break
                            @case('payment') 💰 @break
                            @default 🔔
                        @endswitch
                    </div>

                    <div class="activity-body">
                        <div class="row-title">
                            {{ $a['text'] }}
                        </div>
                        <div class="row-note">
                            {{ $a['ts']->diffForHumans() }} • Activity
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    @else
        <div class="empty">
            <div class="empty-emoji">📭</div>
            <div class="empty-title">Nothing here yet</div>
            <div class="empty-note">
                Once you start booking services or receiving messages, they’ll show up here.
            </div>
        </div>
    @endif
</section>
</div>
@endsection