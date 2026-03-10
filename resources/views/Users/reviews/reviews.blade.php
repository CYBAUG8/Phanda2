@extends('Users.layout')

@section('content')
<div class="page">

    <h1>Provider Reviews</h1>

    <div class="card" style="padding:16px;border-radius:12px;border:1px solid #eee">

        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px">
            <div>
                <h2>
                    Reviews for {{ $selectedProvider['full_name'] ?? 'Provider' }}
                </h2>

                @if($reviewableBooking)
                    <p style="margin-top:6px;color:#666;font-size:14px;">
                        Reviewing completed booking {{ $reviewableBooking->booking_code }}
                        on {{ $reviewableBooking->booking_date->format('d M Y') }}.
                    </p>
                @elseif($selectedProvider)
                    <p style="margin-top:6px;color:#666;font-size:14px;">
                        Viewing public reviews for this provider.
                    </p>
                @endif
            </div>

            <div style="display:flex;gap:8px">
                @if($reviewForBooking)
                    <form method="POST"
                          action="{{ route('reviews.destroy', $reviewForBooking->review_id) }}">
                        @csrf
                        @method('DELETE')
                        <button style="padding:8px 16px;border-radius:25px;border:1px solid #ddd;background:#fff">
                            Delete My Review
                        </button>
                    </form>
                @endif

                @if($reviewableBooking)
                    <button onclick="openModal()"
                            style="padding:8px 16px;border-radius:25px;border:none;background:#ff8c00;color:#fff;font-weight:600">
                        {{ $reviewForBooking ? 'Edit My Review' : '+ Review Provider' }}
                    </button>
                @else
                    <a href="{{ route('users.bookings', ['status' => 'completed']) }}"
                       style="padding:8px 16px;border-radius:25px;border:none;background:#ff8c00;color:#fff;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;">
                        Open Completed Bookings
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div style="padding:12px;background:#d4edda;color:#155724;border-radius:8px;margin-bottom:12px">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="padding:12px;background:#f8d7da;color:#842029;border-radius:8px;margin-bottom:12px">
                {{ session('error') }}
            </div>
        @endif

        @if($bookingContextError)
            <div style="padding:12px;background:#fff3cd;color:#664d03;border-radius:8px;margin-bottom:12px">
                {{ $bookingContextError }}
            </div>
        @endif

        @if(!$reviewableBooking)
            <div style="padding:12px;background:#e9f2ff;color:#0b3d91;border-radius:8px;margin-bottom:12px">
                To leave a review, open a completed booking and click <strong>Review</strong>.
            </div>
        @endif

        <div style="display:flex;gap:16px">
            <aside style="flex:0 0 240px;background:#fff;padding:16px;border:1px solid #eee;border-radius:8px">
                <div style="text-align:center;margin-bottom:10px;display:grid;gap:6px;justify-items:center">
                    <span style="font-size:32px;color:#ff8c00;font-weight:bold">{{ $averageRating }}</span>

                    @include('Users.reviews.partials.stars', ['value' => $averageRating])

                    <small style="color:#666">
                        {{ $totalReviews }} review{{ $totalReviews !== 1 ? 's' : '' }}
                    </small>
                </div>

                <div style="display:grid;gap:6px">
                    @foreach($ratingCounts as $row)
                        @php
                            $pct = $totalReviews ? ($row['count'] / $totalReviews) * 100 : 0;
                        @endphp
                        <div style="display:flex;align-items:center;gap:6px;font-size:14px">
                            <span>{{ $row['star'] }}★</span>
                            <div style="flex:1;height:8px;background:#eee;border-radius:4px;overflow:hidden">
                                <div style="height:100%;width:{{ $pct }}%;background:#ff8c00"></div>
                            </div>
                            <span>{{ $row['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </aside>

            <main style="flex:1">
                <div style="display:flex;flex-direction:column;gap:10px">
                    @forelse($reviews as $review)
                        <article class="card"
                                 style="border:1px solid #eee;border-radius:8px;padding:12px;background:#fff">

                            @include('Users.reviews.partials.stars', ['value' => $review->rating])

                            <p style="margin-top:6px;color:#333;white-space:pre-wrap">
                                {{ $review->comment }}
                            </p>

                            <div style="display:flex;justify-content:space-between">
                                <strong style="color:#6b4f3b">
                                    {{ $review->from_user_id === optional($currentUser)->user_id ? 'You' : ($review->customer->full_name ?? 'Anonymous') }}
                                </strong>
                                <small style="color:#666">
                                    {{ $review->created_at->format('d M Y') }}
                                </small>
                            </div>
                        </article>
                    @empty
                        <div class="card"
                             style="text-align:center;padding:20px;color:#6b7280;border:1px solid #eee;border-radius:8px">
                            No reviews yet. Be the first!
                        </div>
                    @endforelse
                    <div class="d-flex justify-content-center mt-3">
                        {{ $reviews->links('pagination::simple-bootstrap-5') }}
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

@include('Users.reviews.partials.modal')
@endsection

