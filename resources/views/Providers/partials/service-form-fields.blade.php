<div class="mb-3">
    <label class="form-label">Category</label>
    <select name="category_id" class="form-select" required>
        <option value="">Select category</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Service Title</label>
    <input type="text" name="title" class="form-control" required>
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="3" required></textarea>
</div>

<div class="mb-3">
    <label class="form-label">Price (R)</label>
    <input type="number" step="0.01" min="0" name="base_price" class="form-control" required>
</div>

<div class="mb-3">
    <label class="form-label">Minimum Duration (minutes)</label>
    <input type="number" min="15" max="1440" name="min_duration" class="form-control" required>
</div>

<div class="mb-3">
    <label class="form-label">Service Location</label>
    <input type="text" name="location" class="form-control" value="{{ old('location', $providerProfile->service_area ?? '') }}" readonly required>
    <small class="text-muted">Location is linked to your provider service area.</small>
</div>
