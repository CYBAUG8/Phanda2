@extends('users.layout')

@section('content')
    <div class="page-header">
        <h2>Find Services</h2>
        <p>Expert professionals for all your home needs, just a search away.</p>
    </div>

    {{-- Search Section --}}
    <form action="/users/services" method="GET" class="search-section card">
        <div class="search-row">
            <div class="search-input-wrap">
                <i class="fas fa-search search-icon"></i>
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Search services, providers..."
                    value="{{ $filters['search'] }}"
                >
            </div>
            <div class="search-input-wrap search-input-wrap--location">
                <i class="fas fa-map-marker-alt search-icon"></i>
                <input
                    type="text"
                    name="location"
                    class="search-input"
                    placeholder="Location (e.g. Sandton)"
                    value="{{ $filters['location'] }}"
                >
            </div>
            <button type="submit" class="btn-primary">
                <i class="fas fa-search"></i>
                <span>Search</span>
            </button>
        </div>
    </form>

    {{-- Category Pills --}}
    <div class="category-pills-container" style="margin-bottom: 24px; width: 100%;">
        <div class="category-pills">
            <a href="/users/services?{{ http_build_query(array_merge($filters, ['category' => ''])) }}"
               class="category-pill {{ $filters['category'] === '' ? 'category-pill--active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>All Services</span>
            </a>
            @foreach($categories as $cat)
                <a href="/users/services?{{ http_build_query(array_merge($filters, ['category' => $cat->slug])) }}"
                   class="category-pill {{ $filters['category'] === $cat->slug ? 'category-pill--active' : '' }}">
                    <i class="fas {{ $cat->icon }}"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="results-header" style="margin-bottom: 24px;">
        <span class="results-count">
            Showing <strong>{{ $services->total() }}</strong> service{{ $services->total() !== 1 ? 's' : '' }} found
        </span>
        <div class="sort-wrap">
            <label for="sortSelect">Sort by:</label>
            <select id="sortSelect" class="sort-dropdown" onchange="window.location.href=this.value">
                @php
                    $sortOptions = [
                        'rating' => 'Top Rated',
                        'price_asc' => 'Price: Low to High',
                        'price_desc' => 'Price: High to Low',
                        'newest' => 'Newest',
                    ];
                @endphp
                    @foreach($sortOptions as $value => $label)
                        <option value="/users/services?{{ http_build_query(array_merge($filters, ['sort' => $value])) }}"
                                {{ $filters['sort'] === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="results-header" style="margin-bottom: 24px;">
        <span class="results-count">
            Showing <strong>{{ $services->total() }}</strong> curated service{{ $services->total() !== 1 ? 's' : '' }}
        </span>
    </div>

    {{-- Services Grid --}}
    @if($services->count() > 0)
        <div class="services-grid">
            @foreach($services as $service)
                <div class="service-card">
                    <div class="service-card__image">
                        <i class="fas {{ $service->category->icon }}"></i>
                        <span class="service-card__category-badge">{{ $service->category->name }}</span>
                    </div>

                    <div class="service-card__body">
                        <div class="service-card__rating">
                            <div class="stars">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($service->rating))
                                        <i class="fas fa-star"></i>
                                    @elseif($i - 0.5 <= $service->rating)
                                        <i class="fas fa-star-half-alt"></i>
                                    @else
                                        <i class="far fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="rating-text">{{ number_format($service->rating, 1) }} ({{ $service->reviews_count }})</span>
                        </div>
                        <h3 class="service-card__title">{{ $service->title }}</h3>
                        <p class="service-card__provider">
                            <i class="fas fa-user-circle"></i> {{ $service->provider_name }}
                        </p>
                        <p class="service-card__description">{{ Str::limit($service->description, 90) }}</p>
                    </div>

                    <div class="service-card__footer">
                        <div class="service-card__meta">
                            <span class="service-card__price">{{ $service->formatted_price }}</span>
                            <span class="service-card__duration">
                                <i class="far fa-clock"></i> {{ $service->formatted_duration }}
                            </span>
                        </div>
                        <div class="service-card__location">
                            <i class="fas fa-map-marker-alt"></i> {{ $service->location }}
                        </div>
                        <button type="button"
                                class="btn-primary"
                                onclick="openBookingModal({{ $service->id }}, '{{ addslashes($service->title) }}', '{{ $service->formatted_price }}', '{{ addslashes($service->provider_name) }}')">
                            <i class="fas fa-calendar-alt"></i> Book Now
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrap">
            {{ $services->links() }}
        </div>
    @else
        <div class="empty-state card">
            <div class="empty-state__icon">
                <i class="fas fa-search"></i>
            </div>
            <h3>No matches found</h3>
            <p>Try adjusting your filters or search terms.</p>
            <a href="/users/services" class="btn-primary">
                <i class="fas fa-redo"></i> Reset Filters
            </a>
        </div>
    @endif

    {{-- Booking Modal --}}
    <div class="modal-overlay" id="bookingModal">
        <div class="modal">
            <div class="modal__header">
                <h3><i class="fas fa-calendar-check"></i> Complete Booking</h3>
                <button class="modal__close" onclick="closeBookingModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal__service-info">
                <strong id="modalServiceTitle"></strong>
                <span class="muted" id="modalProviderName"></span>
                <div class="service-card__price" id="modalPrice"></div>
            </div>
            <form action="/users/bookings" method="POST" class="modal__form">
                @csrf
                <input type="hidden" name="service_id" id="modalServiceId">

                <div class="grid modal__grid-fix" style="grid-template-columns: 1fr 1fr; margin-bottom: 20px; gap: 16px;">
                    <div class="form-group">
                        <label for="booking_date">
                            <i class="far fa-calendar"></i> Service Date
                        </label>
                        <input type="date" name="booking_date" id="booking_date" class="form-input" required
                               min="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group">
                        <label for="start_time">
                            <i class="far fa-clock"></i> Start Time
                        </label>
                        <input type="time" name="start_time" id="start_time" class="form-input" required
                               min="07:00" max="18:00">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">
                        <i class="fas fa-map-marker-alt"></i> Full Address
                    </label>
                    <input type="text" name="address" id="address" class="form-input" required
                           placeholder="e.g. 12 Main St, Johannesburg">
                </div>

                <div class="form-group">
                    <label for="notes">
                        <i class="fas fa-sticky-note"></i> Specific Notes
                    </label>
                    <textarea name="notes" id="notes" class="form-input form-textarea"
                              placeholder="Any special requests or details..." rows="3"></textarea>
                </div>

                @if($errors->any())
                    <div class="form-errors">
                        @foreach($errors->all() as $error)
                            <p><i class="fas fa-exclamation-triangle"></i> {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="modal__actions">
                    <button type="button" class="btn-outline" style="border-radius: 12px;" onclick="closeBookingModal()">Back</button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-check"></i> Confirm Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openBookingModal(serviceId, title, price, provider) {
        document.getElementById('modalServiceId').value = serviceId;
        document.getElementById('modalServiceTitle').textContent = title;
        document.getElementById('modalPrice').textContent = price;
        document.getElementById('modalProviderName').textContent = provider;
        document.getElementById('bookingModal').classList.add('modal-overlay--active');
        document.body.style.overflow = 'hidden';
    }

    function closeBookingModal() {
        document.getElementById('bookingModal').classList.remove('modal-overlay--active');
        document.body.style.overflow = '';
    }

    // Close modal on overlay click
    document.getElementById('bookingModal').addEventListener('click', function(e) {
        if (e.target === this) closeBookingModal();
    });

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeBookingModal();
    });
</script>
@endpush
