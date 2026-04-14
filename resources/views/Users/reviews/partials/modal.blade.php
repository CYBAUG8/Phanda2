<div id="reviewModal" 
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
            align-items:center;justify-content:center;z-index:1000">

    <div style="background:#fff;padding:24px;border-radius:12px;width:92%;max-width:520px">
        <h2>
            {{ $userReviewForSelected ? 'Edit Your Review' : 'Add Review' }}
        </h2>

        @if($reviewableBooking)
            <p class="mt-2 text-sm text-slate-500">
                Booking {{ $reviewableBooking->booking_code }} on {{ $reviewableBooking->booking_date->format('d M Y') }}
            </p>
        @endif

        <form method="POST" action="{{ route('reviews.store') }}" class="mt-4 space-y-4">
            @csrf

            {{-- REQUIRED hidden fields --}}
            <input type="hidden" name="provider_id" value="{{ $selectedProviderId }}">

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Comment</label>
                <textarea
                    name="comment"
                    rows="4"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100"
                    placeholder="Write your review..."
                >{{ old('comment', optional($reviewForBooking)->comment) }}</textarea>
            </div>

            <input type="hidden" name="user_id" value="{{ $currentUser->user_id }}">

            {{-- Rating --}}
            <label style="font-weight:bold;margin-top:12px;display:block">Rating</label>
            <select name="rating" required style="width:100%;padding:8px">
                <option value="">Select rating</option>
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}"
                        @selected(optional($userReviewForSelected)->rating == $i)>
                        {{ $i }}★
                    </option>
                @endfor
            </select>

            {{-- Comment --}}
            <textarea name="comment"
                      rows="4"
                      required
                      style="width:100%;margin-top:12px;padding:8px"
                      placeholder="Write your review...">{{ old('comment', optional($userReviewForSelected)->comment) }}</textarea>

            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" onclick="closeModal()">Cancel</button>

                <button type="submit"
                        style="background:#ff8c00;color:#fff;border:none;padding:10px 24px;border-radius:25px">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>
