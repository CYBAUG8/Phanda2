@extends('providers.layout')

@section('content')
<<<<<<< HEAD
    <h2>Provider Dashboard</h2>
    <p>Welcome to the Provider Dashboard. This area will show analytics, quick links and summaries.</p>
@endsection
=======
<div class="px-6 py-6">

    <!-- Page Header -->
    <section class="bg-white border rounded-lg p-8 shadow-sm mb-8">
        <div class="flex items-center justify-between mb-6 ">

            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard Overview</h1>
                <p class="text-sm text-gray-500">Welcome back, Here's what's happening today.</p>
            </div>

            <!-- Active Toggle -->
            <div 
                x-data="{
                    active: {{ $isOnline ? 'true' : 'false' }},
                    toggle(){
                        this.active = !this.active

                        fetch('{{ route('provider.toggleOnline') }}', {
                            method:'POST',
                            headers:{
                                'Content-Type':'application/json',
                                'X-CSRF-TOKEN':'{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                is_online:this.active
                            })
                        })
                    }
                }"
                class="flex items-center gap-3 bg-white px-4 py-2 rounded-xl shadow-sm"
            >

            <span 
                class="text-sm font-semibold"
                :class="active ? 'text-orange-600' : 'text-gray-400'"
                x-text="active ? 'Active' : 'Offline'">
            </span>

            <button 
                @click="toggle()"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition"
                :class="active ? 'bg-orange-500' : 'bg-gray-300'"
            >
            <span 
                class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                :class="active ? 'translate-x-6' : 'translate-x-1'">
            </span>
            </button>

            </div>

        </div>
    </section>
    <!-- ===================== -->
    <!-- SUMMARY CARDS -->
    <!-- ===================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

       <!-- Service Rating -->
        <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
            <p class="text-sm text-gray-500">Service Rating</p>

            <div class="flex items-center mt-2">
                <span class="text-2xl font-bold text-yellow-500 mr-2">
                    {{ number_format($averageRating,1) }}
                </span>

                <span class="text-sm text-gray-400">
                    ({{ $totalReviews ?? 0}} reviews)
                </span>
            </div>
        </div>
        <!-- Total Bookings -->
        <a href="{{ url('/providers/bookings') }}" class="block bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
            <p class="text-sm text-gray-500">Total Bookings</p>
            <h2 class="text-2xl font-bold text-gray-800 mt-2">{{ $totalBookings }}</h2>
        </a>
        <!-- Completed -->
        <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
            <p class="text-sm text-gray-500">Completed</p>
            <h2 class="text-2xl font-bold text-green-600 mt-2">{{ $completedBookings }}</h2>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
            <p class="text-sm text-gray-500">Pending</p>
            <h2 class="text-2xl font-bold text-yellow-500 mt-2">{{ $pendingBookings }}</h2>
        </div>

        <!-- Available Balance -->
        <div class="bg-gradient-to-r from-orange-600 to-orange-600 text-white rounded-xl shadow-sm p-5">
            <p class="text-sm opacity-80">Available Balance</p>
            <h2 class="text-2xl font-bold mt-2">R{{ number_format($availableBalance, 2) }}</h2>
        </div>

    </div>
    

    {{-- Recent Bookings --}}
    <section class="card">
        <div class="card-header">
            <div class="card-title">Recent Bookings</div>
        </div>

        @if($recentBookings->count() > 0)
            <div class="list">
                @foreach($recentBookings as $booking)
                    <div class="list-row">
                        {{-- Booking Details --}}
                        <div class="activity-body">
                            <div class="row-title">
                                {{ $booking->service->title ?? 'Service Booking' }}
                            </div>
                            
                            <div class="row-note">
                                R {{ number_format($booking->total_price, 2) }} • 
                                {{ ucfirst(str_replace('_',' ',$booking->status)) }} • 
                                {{ $booking->created_at->diffForHumans() }}
                            </div>
                            <br>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="empty">
                <div class="empty-emoji">📅</div>
                <div class="empty-title">No bookings yet</div>
                <div class="empty-note">Your recent bookings will appear here.</div>
            </div>
        @endif
    </section>

</div>
@endsection
>>>>>>> Lethokuhle
