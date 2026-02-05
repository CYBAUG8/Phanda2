<div id="reviewModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
            align-items:center;justify-content:center;z-index:1000">

    <div style="background:#fff;padding:24px;border-radius:12px;width:92%;max-width:520px">
        <h2 style="color:#6b4f3b">
            {{ $userReviewForSelected ? 'Edit Your Review' : 'Add Review' }}
        </h2>

        <form method="POST" action="{{ route('reviews.store') }}">
            @csrf

            <input type="hidden" name="provider_id" value="{{ $selectedProviderId }}">

            {{-- rating --}}
            <label>Rating</label>
            <select name="rating" required>
                @for($i=1;$i<=5;$i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>

            {{-- comment --}}
            <textarea name="comment"
                      rows="4"
                      style="width:100%;margin-top:10px"
                      placeholder="Write your review..."></textarea>

            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px">
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
