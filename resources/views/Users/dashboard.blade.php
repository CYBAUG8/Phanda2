@extends('users.layout')

@section('content')
<div class="container">
    <h2>Welcome back, {{ $summary->name }}</h2>

    {{-- Stats Grid --}}
    <div class="stats-grid">
        <div class="stat-card card">
            <div class="stat-label">Bookings in Progress</div>
            <div class="stat-value">{{ $summary->bookings_in_progress }}</div>
        </div>

        <div class="stat-card card">
            <div class="stat-label">Unread Messages</div>
            <div class="stat-value">{{ $summary->unread_messages }}</div>
            @if($summary->unread_messages > 0)
                <div class="chip chip-attn">{{ $summary->unread_messages }} unread</div>
            @endif
        </div>

        <div class="stat-card card">
            
            <div class="stat-label" style="display:flex;align-items:center;gap:4px">
                 Average Rating
            </div>
            <span >{{ number_format($summary->average_rating, 1) }}</span>
        </div>
    </div>

    {{-- Recent Activity --}}
    <section class="card">
        <div class="card-header">
            <div class="card-title">Recent Activities</div>
        </div>

        @if(isset($activities) && $activities->isNotEmpty())
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
        @else
            <div class="empty">
                <div class="empty-emoji">🔔</div>
                <div class="empty-title">No activity yet</div>
                <div class="empty-note">Your bookings and messages will appear here.</div>
            </div>
        @endif
    </section>
</div>
@endsection
