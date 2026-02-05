@extends('users.layout')

@section('content')
<div class="page">

    <h1 style="color:#6b4f3b">Provider Reviews</h1>

    <div class="card" style="padding:16px;border-radius:12px;border:1px solid #eee">

        {{-- Header --}}
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px">

            <div>
                <h2 style="margin:0;color:#6b4f3b">
                    Reviews for {{ $selectedProvider['name'] ?? 'Provider' }}
                </h2>

                <form method="GET">
                    <select name="provider"
                            onchange="this.form.submit()"
                            style="margin-top:6px;padding:8px 10px;border-radius:8px;border:1px solid #ddd">
                        @foreach($providers as $provider)
                            <option value="{{ $provider['id'] }}"
                                @selected($provider['id'] === $selectedProviderId)>
                                {{ $provider['name'] }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div style="display:flex;gap:8px">
                @if($userReviewForSelected)
                    <form method="POST"
                          action="{{ route('reviews.destroy', $userReviewForSelected->id) }}">
                        @csrf
                        @method('DELETE')
                        <button style="padding:8px 16px;border-radius:25px;border:1px solid #ddd;background:#fff">
                            Delete My Review
                        </button>
                    </form>
                @endif

                <button onclick="openModal()"
                        style="padding:8px 16px;border-radius:25px;border:none;background:#ff8c00;color:#fff;font-weight:600">
                    {{ $userReviewForSelected ? 'Edit My Review' : '+ Review Provider' }}
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div style="display:flex;gap:16px">

            {{-- Sidebar stats --}}
            @php
                $totalReviews = $reviews->count();
                $average = $totalReviews
                    ? number_format($reviews->avg('rating'), 1)
                    : 0;

                $ratingCounts = collect([5,4,3,2,1])->map(fn($s) => [
                    'star' => $s,
                    'count' => $reviews->where('rating', $s)->count(),
                ]);
            @endphp

            <aside style="flex:0 0 240px;background:#fff;padding:16px;border:1px solid #eee;border-radius:8px">

                <h3 style="margin-top:0;color:#6b4f3b">
                    {{ $selectedProvider['name'] ?? '' }}
                </h3>

                <div style="text-align:center;margin-bottom:10px;display:grid;gap:6px;justify-items:center">
                    <span style="font-size:32px;color:#ff8c00;font-weight:bold">{{ $average }}</span>

                    @include('users.reviews.partials.stars', ['value' => $average])

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

            {{-- Reviews list --}}
            <main style="flex:1">

                <div style="display:flex;flex-direction:column;gap:10px">
                    @forelse($reviews as $review)
                        <article class="card"
                                 style="border:1px solid #eee;border-radius:8px;padding:12px;background:#fff">

                            @include('users.reviews.partials.stars', ['value' => $review->rating])

                            <p style="margin-top:6px;color:#333;white-space:pre-wrap">
                                {{ $review->comment }}
                            </p>

                            <div style="display:flex;justify-content:space-between">
                                <strong style="color:#6b4f3b">
                                    {{ $review->user_id === optional($currentUser)->id ? 'You' : ($review->user->name ?? 'Anonymous') }}
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
                    <div style="margin-top:12px">
                        {{ $reviews->links('pagination::simple-bootstrap-4') }}
                    </div>

                </div>

            </main>
        </div>
    </div>
</div>

@include('users.reviews.partials.modal')
@endsection
