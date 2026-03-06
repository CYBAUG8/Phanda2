@extends('providers.layout')

@section('content')
<div x-data="{
        activeTab: 'pending',
        detailsModalOpen: false,
        selectedBooking: {
            code: '',
            service: '',
            customer: '',
            date: '',
            time: '',
            status: '',
            address: '',
            notes: '',
            price: ''
        }
    }" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Bookings</h1>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-100 flex items-center">
            <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-100 flex items-center">
            <i class="fa-solid fa-circle-exclamation mr-2"></i> {{ session('error') }}
        </div>
    @endif

    @php
        $statusTabs = [
            'pending' => ['label' => 'Pending', 'empty' => 'No pending requests.'],
            'confirmed' => ['label' => 'Confirmed', 'empty' => 'No confirmed bookings.'],
            'in_progress' => ['label' => 'In Progress', 'empty' => 'No in-progress bookings.'],
            'completed' => ['label' => 'Completed', 'empty' => 'No completed bookings.'],
            'cancelled' => ['label' => 'Cancelled', 'empty' => 'No cancelled bookings.'],
        ];

        $statusBadgeClasses = [
            'pending' => 'bg-yellow-100 text-yellow-700',
            'confirmed' => 'bg-blue-100 text-blue-700',
            'in_progress' => 'bg-indigo-100 text-indigo-700',
            'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
        ];
    @endphp

    <div class="mb-6 inline-flex flex-wrap bg-gray-100 rounded-xl p-1 gap-1">
        @foreach($statusTabs as $statusKey => $tab)
            <button @click="activeTab = '{{ $statusKey }}'"
                    :class="activeTab === '{{ $statusKey }}' ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900'"
                    class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors">
                {{ $tab['label'] }} ({{ $bookings->where('status', $statusKey)->count() }})
            </button>
        @endforeach
    </div>

    <div class="space-y-4">
        @foreach($statusTabs as $statusKey => $tab)
            @php
                $statusBookings = $bookings->where('status', $statusKey)->values();
            @endphp

            <div x-show="activeTab === '{{ $statusKey }}'" class="space-y-4" style="display:none;">
                @forelse($statusBookings as $booking)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <p class="text-xs text-gray-500">{{ $booking->booking_code }}</p>
                                <h3 class="text-base font-semibold text-gray-900">{{ $booking->service->title ?? 'Service' }}</h3>
                                <p class="text-sm text-gray-500">{{ $booking->user->full_name ?? 'Unknown User' }}</p>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ optional($booking->booking_date)->format('Y-m-d') }}
                                    {{ $booking->start_time ? \Carbon\Carbon::parse($booking->start_time)->format('H:i') : '' }}
                                </p>
                                <span class="inline-flex mt-2 px-2 py-1 rounded-full text-xs font-semibold {{ $statusBadgeClasses[$booking->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button type="button"
                                        @click="selectedBooking = {
                                            code: @js($booking->booking_code),
                                            service: @js($booking->service->title ?? 'Service'),
                                            customer: @js($booking->user->full_name ?? 'Unknown User'),
                                            date: @js(optional($booking->booking_date)->format('Y-m-d') ?? 'N/A'),
                                            time: @js($booking->start_time ? \Carbon\Carbon::parse($booking->start_time)->format('H:i') : 'N/A'),
                                            status: @js(ucfirst(str_replace('_', ' ', $booking->status))),
                                            address: @js($booking->address ?? 'No address provided'),
                                            notes: @js($booking->notes ?: 'No notes provided'),
                                            price: @js('R' . number_format((float) $booking->total_price, 2))
                                        }; detailsModalOpen = true"
                                        class="px-3 py-2 rounded-lg border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-semibold">
                                    View Details
                                </button>

                                @if($booking->status === 'pending')
                                    <form method="POST" action="{{ route('provider.bookings.confirm', $booking->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">Accept</button>
                                    </form>
                                    <form method="POST" action="{{ route('provider.bookings.cancel', $booking->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">Decline</button>
                                    </form>
                                @elseif($booking->status === 'confirmed')
                                    <form method="POST" action="{{ route('provider.bookings.start', $booking->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Start</button>
                                    </form>
                                    <form method="POST" action="{{ route('provider.bookings.cancel', $booking->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">Cancel</button>
                                    </form>
                                @elseif($booking->status === 'in_progress')
                                    <form method="POST" action="{{ route('provider.bookings.complete', $booking->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">Complete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-10">{{ $tab['empty'] }}</p>
                @endforelse
            </div>
        @endforeach
    </div>

    <div x-show="detailsModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="detailsModalOpen = false"></div>

            <div class="relative z-10 w-full max-w-lg bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Request Details</h3>
                    <button type="button" @click="detailsModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Booking Code</dt>
                        <dd class="font-semibold text-gray-900" x-text="selectedBooking.code"></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd class="font-semibold text-gray-900" x-text="selectedBooking.status"></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Service</dt>
                        <dd class="font-semibold text-gray-900" x-text="selectedBooking.service"></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Customer</dt>
                        <dd class="font-semibold text-gray-900" x-text="selectedBooking.customer"></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Date</dt>
                        <dd class="font-semibold text-gray-900" x-text="selectedBooking.date"></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Start Time</dt>
                        <dd class="font-semibold text-gray-900" x-text="selectedBooking.time"></dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500">Address</dt>
                        <dd class="font-semibold text-gray-900" x-text="selectedBooking.address"></dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500">Notes</dt>
                        <dd class="font-semibold text-gray-900" x-text="selectedBooking.notes"></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Total Price</dt>
                        <dd class="font-semibold text-gray-900" x-text="selectedBooking.price"></dd>
                    </div>
                </dl>

                <div class="mt-6 flex justify-end">
                    <button type="button" @click="detailsModalOpen = false"
                            class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-black">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
