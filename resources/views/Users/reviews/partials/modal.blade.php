<div id="reviewModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
            align-items:center;justify-content:center;z-index:1000">

    <div style="background:#fff;padding:24px;border-radius:12px;width:92%;max-width:520px">
        <h2 >
            {{ $userReviewForSelected ? 'Edit Your Review' : 'Add Review' }}
        </h2>

        <form method="POST" action="{{ route('reviews.store') }}">
            @csrf

            {{-- REQUIRED hidden fields --}}
            <input type="hidden" name="provider_id" value="{{ $selectedProviderId }}">
            
            <input type="hidden" name="service_id" value="{{ $selectedProvider['name']}}">

            <input type="hidden" name="user_id" value="{{ $currentUser->id }}">

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

<script>
function openModal() {
    document.getElementById('reviewModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('reviewModal').style.display = 'none';
}
</script>
