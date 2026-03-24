@extends('Providers.layout')

@section('content')
    <div class="provider-hero">
        <h2>Welcome, Provider</h2>
        <p class="muted">This is your provider home area. Use the sidebar to navigate.</p>
        <div class="mt-4">
            <a href="/provider/logout" class="btn-primary">Logout</a>
        </div>
    </div>

    <section class="stats mt-6">
        <div class="stat card">
            <div class="k">124</div>
            <div class="label">Upcoming Bookings</div>
        </div>
        <div class="stat card">
            <div class="k">R42,800</div>
            <div class="label">Earnings (30d)</div>
        </div>
        <div class="stat card">
            <div class="k">4.9</div>
            <div class="label">Average Rating</div>
        </div>
    </section>
@endsection
