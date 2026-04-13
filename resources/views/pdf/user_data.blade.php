<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; background: #fff; padding: 40px; }

        .header { border-bottom: 3px solid #f97316; padding-bottom: 20px; margin-bottom: 30px; }
        .header-inner { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 28px; font-weight: bold; color: #f97316; }
        .header-meta { text-align: right; color: #6b7280; font-size: 11px; }

        .section { margin-bottom: 28px; }
        .section-title { font-size: 14px; font-weight: bold; color: #f97316; border-bottom: 1px solid #fed7aa; padding-bottom: 6px; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Summary cards */
        .summary-cards { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .summary-cards td { width: 25%; padding: 4px; }
        .card { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 6px; padding: 12px; text-align: center; }
        .card-value { font-size: 18px; font-weight: bold; color: #ea580c; }
        .card-label { font-size: 10px; color: #92400e; margin-top: 2px; }

        .info-grid { width: 100%; border-collapse: collapse; }
        .info-grid td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; }
        .info-grid td:first-child { font-weight: 600; color: #6b7280; width: 40%; }
        .info-grid tr:last-child td { border-bottom: none; }

        .table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .table th { background: #fff7ed; color: #92400e; font-weight: 600; padding: 8px 10px; text-align: left; border-bottom: 2px solid #fed7aa; }
        .table td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; }
        .table tr:last-child td { border-bottom: none; }
        .table td.amount { font-weight: 600; color: #065f46; }
        .table td.right { text-align: right; }

        /* Progress bar for category breakdown */
        .bar-wrap { background: #f3f4f6; border-radius: 4px; height: 8px; width: 100%; }
        .bar-fill { background: #f97316; border-radius: 4px; height: 8px; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .badge-pending   { background: #fef3c7; color: #92400e; }
        .badge-confirmed { background: #dbeafe; color: #1e40af; }
        .badge-completed { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }

        .empty { color: #9ca3af; font-style: italic; font-size: 11px; padding: 8px 0; }
        .text-right { text-align: right; }
        .text-muted { color: #6b7280; }
        .mt-4 { margin-top: 4px; }

        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 10px; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <div class="header-inner">
            <div class="logo">Phanda</div>
            <div class="header-meta">
                <div>My Account Data</div>
                <div>Generated: {{ now()->format('d M Y, H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- PERSONAL INFORMATION --}}
    <div class="section">
        <div class="section-title">Personal Information</div>
        <table class="info-grid">
            <tr><td>Full Name</td><td>{{ $user->full_name ?? '—' }}</td></tr>
            <tr><td>Email</td><td>{{ $user->email ?? '—' }}</td></tr>
            <tr><td>Phone</td><td>{{ $user->phone ?? '—' }}</td></tr>
            <tr><td>Role</td><td>{{ ucfirst(strtolower($user->role ?? 'Customer')) }}</td></tr>
            <tr><td>Member Since</td><td>{{ $user->created_at?->format('d M Y') ?? '—' }}</td></tr>
        </table>
    </div>

    {{-- FINANCIAL OVERVIEW --}}
    <div class="section">
        <div class="section-title">Financial Overview</div>

        {{-- Summary Cards --}}
        <table class="summary-cards">
            <tr>
                <td>
                    <div class="card">
                        <div class="card-value">R{{ number_format((float)$totalSpent, 2) }}</div>
                        <div class="card-label">Total Spent (All Time)</div>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <div class="card-value">{{ $completedBookings->count() }}</div>
                        <div class="card-label">Completed Bookings</div>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <div class="card-value">{{ $pendingBookings->count() }}</div>
                        <div class="card-label">Pending / Upcoming</div>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <div class="card-value">
                            R{{ $completedBookings->count() > 0 ? number_format((float)($totalSpent / $completedBookings->count()), 2) : '0.00' }}
                        </div>
                        <div class="card-label">Avg. Per Booking</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- BREAKDOWN BY CATEGORY --}}
    <div class="section">
        <div class="section-title">Spending by Category</div>
        @if($categoryBreakdown->isNotEmpty())
            <table class="table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Bookings</th>
                        <th>Total Spent</th>
                        <th style="width:30%">Share</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categoryBreakdown as $category => $data)
                        @php
                            $pct = $totalSpent > 0 ? round(($data['total'] / $totalSpent) * 100) : 0;
                        @endphp
                        <tr>
                            <td>{{ $category }}</td>
                            <td>{{ $data['count'] }}</td>
                            <td class="amount">R{{ number_format((float)$data['total'], 2) }}</td>
                            <td>
                                <div class="bar-wrap">
                                    <div class="bar-fill" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="mt-4 text-muted" style="font-size:10px">{{ $pct }}%</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty">No completed bookings to show category breakdown.</p>
        @endif
    </div>

    {{-- MONTHLY SPENDING --}}
    <div class="section">
        <div class="section-title">Monthly Spending (Last 12 Months)</div>
        @if($monthlySpending->isNotEmpty())
            <table class="table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Bookings</th>
                        <th class="text-right">Amount Spent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlySpending as $month => $data)
                        <tr>
                            <td>{{ $month }}</td>
                            <td>{{ $data['count'] }}</td>
                            <td class="amount right">R{{ number_format((float)$data['total'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background:#fff7ed;">
                        <td><strong>Total</strong></td>
                        <td><strong>{{ $monthlySpending->sum('count') }}</strong></td>
                        <td class="amount right"><strong>R{{ number_format((float)$monthlySpending->sum('total'), 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        @else
            <p class="empty">No spending data in the last 12 months.</p>
        @endif
    </div>

    {{-- PENDING / UPCOMING PAYMENTS --}}
    <div class="section">
        <div class="section-title">Pending & Upcoming Payments</div>
        @if($pendingBookings->isNotEmpty())
            <table class="table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Address</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingBookings->sortBy('booking_date') as $booking)
                        <tr>
                            <td>{{ $booking->service->title ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($booking->address, 35) }}</td>
                            <td class="amount">R{{ number_format((float)$booking->total_price, 2) }}</td>
                            <td><span class="badge badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                        </tr>
                    @endforeach
                    <tr style="background:#fff7ed;">
                        <td colspan="4"><strong>Total Pending</strong></td>
                        <td class="amount"><strong>R{{ number_format((float)$pendingBookings->sum('total_price'), 2) }}</strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        @else
            <p class="empty">No pending or upcoming payments.</p>
        @endif
    </div>

    {{-- ALL BOOKINGS --}}
    <div class="section">
        <div class="section-title">All Booking History</div>
        @if($bookings->isNotEmpty())
            <table class="table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Address</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td>{{ $booking->service->title ?? '—' }}</td>
                            <td>{{ $booking->service?->category?->name ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($booking->address, 30) }}</td>
                            <td class="amount">R{{ number_format((float)$booking->total_price, 2) }}</td>
                            <td><span class="badge badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty">No bookings found.</p>
        @endif
    </div>

    {{-- EMERGENCY CONTACT --}}
    <div class="section">
        <div class="section-title">Emergency Contact</div>
        @if($emergencyContact)
            <table class="info-grid">
                <tr><td>Name</td><td>{{ $emergencyContact->name }}</td></tr>
                <tr><td>Phone</td><td>{{ $emergencyContact->phone }}</td></tr>
                <tr><td>Relationship</td><td>{{ $emergencyContact->relationship ?? '—' }}</td></tr>
            </table>
        @else
            <p class="empty">No emergency contact added.</p>
        @endif
    </div>

    {{-- SAVED LOCATIONS --}}
    <div class="section">
        <div class="section-title">Saved Locations</div>
        @if($locations->isNotEmpty())
            <table class="table">
                <thead>
                    <tr><th>Name</th><th>Address</th><th>Type</th></tr>
                </thead>
                <tbody>
                    @foreach($locations as $loc)
                        <tr>
                            <td>{{ $loc->name }}</td>
                            <td>{{ $loc->address }}</td>
                            <td>{{ ucfirst($loc->type ?? '—') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty">No saved locations.</p>
        @endif
    </div>

    {{-- LOGIN HISTORY --}}
    <div class="section">
        <div class="section-title">Recent Login History (Last 10)</div>
        @if($loginHistories->isNotEmpty())
            <table class="table">
                <thead>
                    <tr><th>Date & Time</th><th>Device</th><th>IP Address</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach($loginHistories as $login)
                        <tr>
                            <td>{{ $login->created_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td>{{ $login->device ?? '—' }}</td>
                            <td>{{ $login->ip_address ?? '—' }}</td>
                            <td>
                                <span class="badge {{ ($login->status ?? '') === 'success' ? 'badge-completed' : 'badge-cancelled' }}">
                                    {{ ucfirst($login->status ?? '—') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty">No login history found.</p>
        @endif
    </div>

    <div class="footer">
        This document was generated by Phanda on {{ now()->format('d M Y') }} and contains your personal account data.
    </div>

</body>
</html>