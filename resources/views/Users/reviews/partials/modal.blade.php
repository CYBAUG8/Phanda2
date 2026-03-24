<div id="reviewModal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-950/50 p-4">
    <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl sm:p-6">
        <h2 class="text-xl font-semibold text-slate-900">
            {{ $reviewForBooking ? 'Edit Your Review' : 'Add Review' }}
        </h2>

        @if($reviewableBooking)
            <p class="mt-2 text-sm text-slate-500">
                Booking {{ $reviewableBooking->booking_code }} on {{ $reviewableBooking->booking_date->format('d M Y') }}
            </p>
        @endif

        <form method="POST" action="{{ route('reviews.store') }}" class="mt-4 space-y-4">
            @csrf

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

            <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeModal()" class="ui-btn-secondary justify-center">
                    Cancel
                </button>
                <button type="submit" class="ui-btn-primary justify-center">
                    Submit Review
                </button>
            </div>
        </form>
    </div>
</div>

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
