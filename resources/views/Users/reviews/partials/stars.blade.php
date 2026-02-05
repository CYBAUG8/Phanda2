@php
    $rounded = round($value);
@endphp

<div style="display:flex;gap:5px">
    @for($i = 1; $i <= 5; $i++)
        <svg width="18" height="18" viewBox="0 0 24 24">
            <path
                d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"
                fill="{{ $i <= $rounded ? '#ffc107' : '#e4e5e9' }}"
            />
        </svg>
    @endfor
</div>
