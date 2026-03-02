@extends('providers.layout')

@section('content')
<div class="container py-4">

    <h2 class="mb-4 fw-bold">Provider Dashboard</h2>

    {{-- SUMMARY CARDS --}}
    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <h6>Total Bookings</h6>
                <h3 class="fw-bold">0</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <h6>Completed</h6>
                <h3 class="fw-bold text-success">0</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <h6>Pending</h6>
                <h3 class="fw-bold text-warning">0</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <h6>Available Balance</h6>
                <h3 class="fw-bold text-primary">
                    R0
                </h3>
            </div>
        </div>

    </div>

    {{-- EARNINGS BREAKDOWN --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3 fw-bold">Earnings Breakdown</h5>

            <div class="row">
                <div class="col-md-3">
                    <p>Total Revenue</p>
                    <h5>R 0</h5>
                </div>

                <div class="col-md-3">
                    <p>Platform Commission (10%)</p>
                    <h5 class="text-danger">
                        - R 0
                    </h5>
                </div>

                <div class="col-md-3">
                    <p>Net Earnings</p>
                    <h5 class="text-success">
                        R 0
                    </h5>
                </div>

                <div class="col-md-3">
                    <p>Total Paid Out</p>
                    <h5>
                        R 0
                    </h5>
                </div>
            </div>
        </div>
    </div>

    {{-- RECENT BOOKINGS --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3 fw-bold">Recent Bookings</h5>

            //$recentBookings->count()
            @if(0 > 0)
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentBookings as $booking)
                        <tr>
                            <td>0</td>
                            <td>{{ $booking->service->title ?? 'N/A' }}</td>
                            <td>
                                <span class="badge 
                                    @if($booking->status == 'completed') bg-success
                                    @elseif($booking->status == 'pending') bg-warning
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td>R {{ number_format($booking->total_price, 2) }}</td>
                            <td>{{ $booking->created_at->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">No bookings yet.</p>
            @endif
        </div>
    </div>

</div>
@endsection
