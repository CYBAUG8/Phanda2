@extends('providers.layout')

@section('content')
<div class="px-6 py-6">

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">

        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Overview</h1>
            <p class="text-sm text-gray-500">Welcome back, Here's what's happening today.</p>
        </div>

        <!-- Active Toggle -->
        <div 
            x-data="{ active: true }"
            class="flex items-center gap-3 bg-white px-4 py-2 rounded-xl shadow-sm"
        >
            <span 
                class="text-sm font-semibold"
                :class="active ? 'text-green-600' : 'text-gray-400'"
                x-text="active ? 'Active' : 'Offline'">
            </span>

            <button 
                @click="active = !active"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition"
                :class="active ? 'bg-green-500' : 'bg-gray-300'"
            >
                <span 
                    class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                    :class="active ? 'translate-x-6' : 'translate-x-1'">
                </span>
            </button>
        </div>

    </div>

    <!-- ===================== -->
    <!-- SUMMARY CARDS -->
    <!-- ===================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

        <!-- Service Rating -->
        <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
            <p class="text-sm text-gray-500">Service Rating</p>

            <div class="flex items-center mt-2">
                <span class="text-2xl font-bold text-yellow-500 mr-2">
                    4.8
                </span>
            </div>
        </div>
        <!-- Total Bookings -->
        <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
            <p class="text-sm text-gray-500">Total Bookings</p>
            <h2 class="text-2xl font-bold text-gray-800 mt-2">0</h2>
        </div>

        <!-- Completed -->
        <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
            <p class="text-sm text-gray-500">Completed</p>
            <h2 class="text-2xl font-bold text-green-600 mt-2">0</h2>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
            <p class="text-sm text-gray-500">Pending</p>
            <h2 class="text-2xl font-bold text-yellow-500 mt-2">0</h2>
        </div>

        <!-- Balance -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl shadow-sm p-5">
            <p class="text-sm opacity-80">Available Balance</p>
            <h2 class="text-2xl font-bold mt-2">R0</h2>
        </div>

    </div>

    <!-- ===================== -->
    <!-- Recent Bookings -->
    <!-- ===================== -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Recent Bookings</h3>

        @if(0 > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-3">#</th>
                        <th class="py-3">Service</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Amount</th>
                        <th class="py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($recentBookings as $booking)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3">0</td>
                        <td class="py-3">{{ $booking->service->title ?? 'N/A' }}</td>
                        <td class="py-3">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                @if($booking->status == 'completed') bg-green-100 text-green-600
                                @elseif($booking->status == 'pending') bg-yellow-100 text-yellow-600
                                @else bg-gray-100 text-gray-600
                                @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td class="py-3">R {{ number_format($booking->total_price, 2) }}</td>
                        <td class="py-3">{{ $booking->created_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="text-center py-10">
                <p class="text-gray-400">No bookings yet.</p>
            </div>
        @endif
    </div>

</div>
@endsection