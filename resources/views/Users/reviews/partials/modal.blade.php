<<<<<<< HEAD
<div id="reviewModal" 
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
            align-items:center;justify-content:center;z-index:1000">

    <div style="background:#fff;padding:24px;border-radius:12px;width:92%;max-width:520px">
        <h2>
            {{ $userReviewForSelected ? 'Edit Your Review' : 'Add Review' }}
=======
<div id="reviewModal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-950/50 p-4">
    <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl sm:p-6">
        <h2 class="text-xl font-semibold text-slate-900">
            {{ $reviewForBooking ? 'Edit Your Review' : 'Add Review' }}
>>>>>>> feature2
        </h2>

        @if($reviewableBooking)
            <p class="mt-2 text-sm text-slate-500">
                Booking {{ $reviewableBooking->booking_code }} on {{ $reviewableBooking->booking_date->format('d M Y') }}
            </p>
        @endif

        <form method="POST" action="{{ route('reviews.store') }}" class="mt-4 space-y-4">
            @csrf

<<<<<<< HEAD
            {{-- REQUIRED hidden fields --}}
            <input type="hidden" name="provider_id" value="{{ $selectedProviderId }}">
=======
            <input type="hidden" name="booking_id" value="{{ optional($reviewableBooking)->id }}">

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Rating</label>
                <select name="rating" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    <option value="">Select rating</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected(optional($reviewForBooking)->rating == $i)>
                            {{ $i }}★
                        </option>
                    @endfor
                </select>
            </div>
>>>>>>> feature2

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

<<<<<<< HEAD
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
=======
            <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeModal()" class="ui-btn-secondary justify-center">
                    Cancel
                </button>
                <button type="submit" class="ui-btn-primary justify-center">
                    Submit Review
>>>>>>> feature2
                </button>
            </div>
        </form>
    </div>
</div>
<<<<<<< HEAD
=======

<script>
function openModal() {
    const modal = document.getElementById('reviewModal');
    if (!modal) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('reviewModal');
    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

document.getElementById('reviewModal')?.addEventListener('click', function (event) {
    if (event.target === this) {
        closeModal();
    }
});
</script>
>>>>>>> feature2
