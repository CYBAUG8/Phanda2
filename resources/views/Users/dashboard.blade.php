@extends('users.layout')

@section('content')
    <div class="container">
    <h2>User Dashboard</h2>
    <p>Welcome to your dashboard.</p>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card card">
            <div class="stat-label">Upcoming</div>
            <div class="stat-value">Bookings</div>
            @if($unread > 0)
                <div class="chip chip-attn">{{ $unread }} unread</div>
            @endif
        </div>

        <div class="stat-card card">
            <div class="stat-label">Messages</div>
            <div class="stat-value">{{ $messages->count() }}</div>
        </div>

        <div class="stat-card card">
            <div class="stat-label">Balance</div>
            <div class="stat-value">R{{ number_format($balance) }}</div>
        </div>
    </div>

    {{-- Activity --}}
    <section class="card">
        <div class="card-header">
            <div class="card-title">Recent Activities</div>
        </div>

        @if($activities->isEmpty())
            <div class="empty">
                <div class="empty-emoji">🔔</div>
                <div class="empty-title">No activity yet</div>
            </div>
        @else
            <div class="list">
                @foreach($activities as $a)
                    <div class="list-row">
                        <div class="activity-icon activity-{{ $a['type'] }}">
                            @switch($a['type'])
                                @case('booking') 📅 @break
                                @case('message') ✉️ @break
                                @case('payment') 💳 @break
                                @default 🔔
                            @endswitch
                        </div>

                        <div class="activity-body">
                            <div class="row-title">{{ $a['text'] }}</div>
                            <div class="row-note">{{ $a['ts']->diffForHumans() }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
