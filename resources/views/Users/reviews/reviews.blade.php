@extends('Users.layout')

@section('content')
<div class="user-page-shell space-y-6">
    <section class="user-page-header">
        <div>
            <h1>Provider Reviews</h1>
            <p class="user-page-subtitle">View provider feedback and submit reviews for completed bookings.</p>
        </div>
        <a href="{{ route('users.bookings', ['status' => 'completed']) }}" class="ui-btn-secondary">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Completed Bookings</span>
        </a>
    </section>

    @include('partials.ui.flash')

    @if($bookingContextError)
        <div class="ui-alert mb-0 border border-amber-200 bg-amber-50 text-amber-800">
            {{ $bookingContextError }}
        </div>
    @endif

    @if(!$reviewableBooking)
        <div class="ui-alert mb-0 border border-blue-200 bg-blue-50 text-blue-800">
            To leave a review, open a completed booking and click <strong>Review</strong>.
        </div>
    @endif

    <div class="ui-card p-4 sm:p-5">
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    Reviews for {{ $selectedProvider['full_name'] ?? 'Provider' }}
                </h2>

                @if($reviewableBooking)
                    <p class="mt-2 text-sm text-slate-500">
                        Reviewing completed booking {{ $reviewableBooking->booking_code }}
                        on {{ $reviewableBooking->booking_date->format('d M Y') }}.
                    </p>
                @elseif($selectedProvider)
                    <p class="mt-2 text-sm text-slate-500">
                        Viewing public reviews for this provider.
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                @if($reviewForBooking)
                    <form method="POST" action="{{ route('reviews.destroy', $reviewForBooking->review_id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ui-btn-secondary">
                            Delete My Review
                        </button>
                    </form>
                @endif

                @if($reviewableBooking)
                    <button type="button" onclick="openModal()" class="ui-btn-primary">
                        {{ $reviewForBooking ? 'Edit My Review' : 'Review Provider' }}
                    </button>
                @else
                    <a href="{{ route('users.bookings', ['status' => 'completed']) }}" class="ui-btn-primary">
                        Open Completed Bookings
                    </a>
                @endif
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-[260px_1fr]">
            <aside class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="mb-3 grid justify-items-center gap-1.5 text-center">
                    <span class="text-4xl font-bold text-orange-600">{{ $averageRating }}</span>
                    @include('Users.reviews.partials.stars', ['value' => $averageRating])
                    <small class="text-sm text-slate-500">
                        {{ $totalReviews }} review{{ $totalReviews !== 1 ? 's' : '' }}
                    </small>
                </div>

                <div class="space-y-2">
                    @foreach($ratingCounts as $row)
                        @php
                            $pct = $totalReviews ? ($row['count'] / $totalReviews) * 100 : 0;
                        @endphp
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-7 text-slate-700">{{ $row['star'] }}★</span>
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200">
                                <div class="h-full rounded-full bg-orange-500" style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="w-6 text-right text-slate-500">{{ $row['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </aside>

            <main>
                <div class="space-y-3">
                    @forelse($reviews as $review)
                        <article class="rounded-xl border border-slate-200 bg-white p-4">
                            @include('Users.reviews.partials.stars', ['value' => $review->rating])

                            <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">
                                {{ $review->comment }}
                            </p>

                            <div class="mt-3 flex items-center justify-between text-sm">
                                <strong class="text-slate-800">
                                    {{ $review->from_user_id === optional($currentUser)->user_id ? 'You' : ($review->customer->full_name ?? 'Anonymous') }}
                                </strong>
                                <small class="text-slate-500">
                                    {{ $review->created_at->format('d M Y') }}
                                </small>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-8 text-center text-sm text-slate-500">
                            No reviews yet. Be the first!
                        </div>
                    @endforelse

                    <div class="mt-3 flex justify-center">
                        {{ $reviews->links('pagination::simple-tailwind') }}
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

@include('Users.reviews.partials.modal')
@endsection
