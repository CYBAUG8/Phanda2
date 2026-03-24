@if(session('success'))
    <div class="ui-alert ui-alert-success mb-4 flex items-start gap-2">
        <i class="fa-solid fa-circle-check mt-0.5"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="ui-alert ui-alert-error mb-4 flex items-start gap-2">
        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="ui-alert ui-alert-error mb-4">
        <p class="mb-2 font-semibold">Please fix the following:</p>
        <ul class="list-disc space-y-1 pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
