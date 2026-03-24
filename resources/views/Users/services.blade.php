@extends('Users.layout')

@push('styles')
<style>
    .services-page {
        overflow-x: clip;
    }

    .services-page .search-section {
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        background: #fff;
        padding: 1rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
    }

    .services-page .search-row {
        display: grid;
        grid-template-columns: 2.2fr 1.1fr 0.95fr;
        gap: 0.85rem;
        align-items: end;
    }

    .services-page .search-row + .search-row {
        margin-top: 0.85rem;
        grid-template-columns: 180px auto 1fr;
    }

    .services-page .search-section .form-label {
        display: block;
        margin-bottom: 0.3rem;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        color: #475569;
        text-transform: uppercase;
    }

    .services-page .search-row-actions {
        display: flex;
        align-items: end;
        gap: 0.75rem;
    }

    .services-page .search-row-actions .ui-btn-primary,
    .services-page .search-row-actions .ui-btn-secondary {
        width: 100%;
        min-height: 2.75rem;
        white-space: nowrap;
    }

    .services-page .category-pills-container,
    .services-page .category-pills,
    .services-page .booking-modal,
    .services-page .location-autocomplete-dropdown {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .services-page .services-grid {
        gap: 1rem;
    }

    .services-page .service-card {
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .services-page .service-card:hover {
        transform: translateY(-2px);
        border-color: #cbd5e1;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    }

    .services-page .service-card__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        border-bottom: 1px solid #f1f5f9;
        padding: 1rem 1rem 0.8rem;
    }

    .services-page .service-card__provider-group {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 0.7rem;
    }

    .services-page .service-card__provider-initial {
        display: inline-flex;
        width: 2.2rem;
        height: 2.2rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #c2410c;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .services-page .service-card__provider-name {
        margin: 0;
        font-size: 0.84rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.25;
    }

    .services-page .service-card__provider-rating {
        margin: 0.18rem 0 0;
        font-size: 0.76rem;
        color: #64748b;
        line-height: 1.25;
    }

    .services-page .service-card__category-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.2rem 0.58rem;
        border: 1px solid #fdba74;
        background: #fff;
        color: #c2410c;
        font-size: 0.68rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .services-page .service-card__body {
        padding: 0.9rem 1rem 0;
    }

    .services-page .service-card__title {
        margin: 0 0 0.45rem;
        font-size: 1.02rem;
        line-height: 1.35;
    }

    .services-page .service-card__description {
        margin: 0;
        color: #475569;
        font-size: 0.88rem;
        line-height: 1.45;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .services-page .service-card__location {
        margin-top: 0.6rem;
        font-size: 0.8rem;
        color: #64748b;
        line-height: 1.35;
    }

    .services-page .service-card__footer {
        padding: 0.9rem 1rem 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .services-page .service-card__meta {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .services-page .service-card__price {
        font-size: 1.7rem;
        letter-spacing: -0.015em;
        line-height: 1;
    }

    .services-page .service-card__duration {
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
    }

    .services-page .service-card__actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .services-page .service-card__reviews-link {
        display: inline-flex;
        align-items: center;
        font-size: 0.76rem;
        font-weight: 600;
        color: #ea580c;
        text-decoration: none;
        white-space: nowrap;
        padding: 0 0.35rem;
    }

    .services-page .service-card__reviews-link:hover {
        color: #c2410c;
    }

    .services-page .category-pills-container::-webkit-scrollbar,
    .services-page .category-pills::-webkit-scrollbar,
    .services-page .booking-modal::-webkit-scrollbar,
    .services-page .location-autocomplete-dropdown::-webkit-scrollbar {
        width: 0;
        height: 0;
        display: none;
    }

    .services-page .results-header {
        margin-bottom: 0.25rem;
    }

    .services-page .sort-wrap {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .services-page .sort-wrap label {
        font-size: 0.85rem;
        color: #64748b;
    }

    .services-page .sort-dropdown {
        min-height: 2.75rem;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        padding: 0.56rem 0.75rem;
        font-size: 0.875rem;
        color: #0f172a;
        background: #fff;
    }

    .services-page .sort-dropdown:focus {
        outline: none;
        border-color: #fb923c;
        box-shadow: 0 0 0 3px rgba(251, 146, 60, 0.2);
    }

    @media (max-width: 700px) {
        .services-page .search-row,
        .services-page .search-row + .search-row {
            grid-template-columns: 1fr;
        }

        .services-page .search-row-actions {
            align-items: stretch;
        }

        .services-page .search-row-actions .ui-btn-primary,
        .services-page .search-row-actions .ui-btn-secondary {
            width: 100%;
            white-space: normal;
        }

        .services-page .sort-wrap {
            width: 100%;
        }

        .services-page .sort-dropdown {
            width: 100%;
        }

        .services-page .service-card__head {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.55rem;
        }

        .services-page .service-card__actions {
            flex-direction: column;
            align-items: stretch;
        }

        .services-page .service-card__reviews-link {
            padding-left: 0;
            padding-right: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="user-page-shell services-page space-y-6">
<section class="user-page-header">
    <div>
        <h1>Find Services</h1>
        <p class="user-page-subtitle">Find nearby providers and send requests quickly.</p>
    </div>
</section>

@include('partials.ui.flash')

@if(!empty($showProximityWarning))
    <div class="ui-alert ui-alert-error flex items-start gap-2">
        <i class="fas fa-location-crosshairs mt-0.5"></i>
        <span>Set your location to see providers within your selected radius.</span>
    </div>
@endif

<form action="{{ route('users.services') }}" method="GET" class="search-section" id="serviceSearchForm">
    <input type="hidden" name="lat" id="searchLat" value="{{ $filters['lat'] ?? '' }}">
    <input type="hidden" name="lng" id="searchLng" value="{{ $filters['lng'] ?? '' }}">

    <div class="search-row">
        <div>
            <label for="serviceSearchInput" class="form-label">Search</label>
            <input
                type="text"
                id="serviceSearchInput"
                name="search"
                class="user-input"
                placeholder="Search services or providers"
                value="{{ $filters['search'] }}"
            >
        </div>

        <div class="search-input-wrap search-input-wrap--location">
            <label for="filterLocation" class="form-label">Location</label>
            <input
                type="text"
                name="location"
                id="filterLocation"
                class="user-input"
                placeholder="City or suburb"
                value="{{ $filters['location'] }}"
                autocomplete="off"
            >
        </div>

        <div class="search-row-actions">
            <button type="submit" class="ui-btn-primary justify-center">
                <span>Search</span>
            </button>
        </div>
    </div>

    <div class="search-row">
        <div>
            <label for="radius_km" class="form-label">Radius (km)</label>
            <input type="number" min="1" max="100" class="user-input" name="radius_km" id="radius_km" value="{{ $filters['radius_km'] ?? 25 }}">
        </div>

        <div class="search-row-actions">
            <button type="button" class="ui-btn-secondary justify-center" id="detectLocationBtn">
                <span>Use Current Location</span>
            </button>
        </div>

        <div class="hidden">
            <small class="text-muted" id="locationStatusText" aria-live="polite"></small>
        </div>
    </div>
</form>

<div class="category-pills-container">
    <div class="category-pills">
        <a href="{{ route('users.services', array_merge($filters, ['category' => ''])) }}" class="category-pill {{ $filters['category'] === '' ? 'category-pill--active' : '' }}">
            <i class="fas fa-th-large"></i><span>All</span>
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('users.services', array_merge($filters, ['category' => $cat->slug])) }}" class="category-pill {{ $filters['category'] === $cat->slug ? 'category-pill--active' : '' }}">
                <i class="fas {{ $cat->icon }}"></i><span>{{ $cat->name }}</span>
            </a>
        @endforeach
    </div>
</div>

<div class="results-header flex items-center justify-between gap-3">
    <span class="text-sm text-slate-500">Showing <strong class="text-slate-900">{{ $services->total() }}</strong> services</span>
    <div class="sort-wrap">
        <label for="sortSelect">Sort by:</label>
        <select id="sortSelect" class="sort-dropdown" onchange="window.location.href=this.value">
            @php
                $sortOptions = [
                    'nearest' => 'Nearest',
                    'rating' => 'Top Rated',
                    'price_asc' => 'Price: Low to High',
                    'price_desc' => 'Price: High to Low',
                    'newest' => 'Newest',
                ];
            @endphp
            @foreach($sortOptions as $value => $label)
                <option value="{{ route('users.services', array_merge($filters, ['sort' => $value])) }}" {{ $filters['sort'] === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
</div>

@if($services->count() > 0)
    <div class="services-grid">
        @foreach($services as $service)
            @php
                $ratingValue = (float) ($service->live_rating ?? $service->rating ?? 0);
                $reviewCount = (int) ($service->live_reviews_count ?? $service->reviews_count ?? 0);
                $providerInitial = strtoupper(substr((string) ($service->provider_name ?? 'P'), 0, 1));
            @endphp
            <div class="service-card">
                <div class="service-card__head">
                    <div class="service-card__provider-group">
                        <span class="service-card__provider-initial">{{ $providerInitial !== '' ? $providerInitial : 'P' }}</span>
                        <div class="min-w-0">
                            <p class="service-card__provider-name">{{ $service->provider_name }}</p>
                            @if($reviewCount > 0)
                                <p class="service-card__provider-rating">
                                    {{ number_format($ratingValue, 1) }}/5 · {{ number_format($reviewCount) }} review{{ $reviewCount === 1 ? '' : 's' }}
                                </p>
                            @else
                                <p class="service-card__provider-rating">No reviews yet</p>
                            @endif
                        </div>
                    </div>
                    <span class="service-card__category-pill">{{ optional($service->category)->name ?? 'General' }}</span>
                </div>

                <div class="service-card__body">
                    <h3 class="service-card__title">{{ $service->title }}</h3>
                    <p class="service-card__description">{{ \Illuminate\Support\Str::limit($service->description, 90) }}</p>

                    <div class="service-card__location">
                        @if(isset($service->distance_km))
                            {{ number_format((float) $service->distance_km, 1) }} km away
                        @else
                            {{ $service->location }}
                        @endif
                    </div>
                </div>

                <div class="service-card__footer">
                    <div class="service-card__meta">
                        <span class="service-card__price">R{{ number_format((float) $service->base_price, 2) }}</span>
                        <span class="service-card__duration">{{ $service->formatted_duration }}</span>
                    </div>

                    <div class="service-card__actions">
                        @if($reviewCount > 0)
                            <a href="{{ route('reviews.reviews', ['provider' => $service->provider_id]) }}" class="service-card__reviews-link">
                                <span>View reviews</span>
                            </a>
                        @endif

                        <button type="button" class="ui-btn-primary w-full justify-center" onclick='openBookingModal(@json($service->service_id), @json($service->title), @json($service->formatted_price), @json($service->provider_name))'>
                            Request Service
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pagination-wrap">{{ $services->links() }}</div>
@else
    <div class="user-empty-state">
        <div class="empty-state__icon"><i class="fas fa-search"></i></div>
        <h3>No nearby services found</h3>
        <p>Try increasing your radius or updating your location.</p>
        <a href="{{ route('users.services') }}" class="ui-btn-primary mt-4"><i class="fas fa-redo"></i> Reset</a>
    </div>
@endif

<div class="modal-overlay" id="bookingModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="bookingModalTitle">
    <div class="booking-modal provider-modal-panel">
        <div class="modal__header">
            <h3 id="bookingModalTitle"><i class="fas fa-calendar-check"></i> Send Service Request</h3>
            <button class="modal__close" type="button" onclick="closeBookingModal()"><i class="fas fa-times"></i></button>
        </div>

        <div class="modal__service-info">
            <strong id="modalServiceTitle"></strong>
            <span class="muted" id="modalProviderName"></span>
            <div class="service-card__price" id="modalPrice"></div>
        </div>

        <form action="{{ route('users.bookings.store') }}" method="POST" class="modal__form">
            @csrf
            <input type="hidden" name="service_id" id="modalServiceId">
            <input type="hidden" name="search_lat" id="bookingSearchLat" value="{{ $filters['lat'] ?? '' }}">
            <input type="hidden" name="search_lng" id="bookingSearchLng" value="{{ $filters['lng'] ?? '' }}">
            <input type="hidden" name="radius_km" id="bookingRadiusKm" value="{{ $filters['radius_km'] ?? 25 }}">

            <div class="grid modal__grid-fix">
                <div class="form-group">
                    <label for="booking_date"><i class="far fa-calendar"></i> Service Date</label>
                    <input type="date" name="booking_date" id="booking_date" class="user-input" min="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label for="start_time"><i class="far fa-clock"></i> Start Time</label>
                    <input type="time" name="start_time" id="start_time" class="user-input" min="07:00" max="18:00" required>
                </div>
            </div>

            <div class="form-group">
                <label for="address"><i class="fas fa-map-marker-alt"></i> Full Address</label>
                <input type="text" name="address" id="address" class="user-input" placeholder="Street, suburb, city" required>
                <button type="button" class="ui-btn-secondary mt-2" id="fillBookingLocationBtn">
                    <i class="fas fa-location-crosshairs"></i> Use Current Location
                </button>
            </div>

            <div class="form-group">
                <label for="notes"><i class="fas fa-sticky-note"></i> Notes</label>
                <textarea name="notes" id="notes" class="user-textarea" rows="3" placeholder="Any special details"></textarea>
            </div>

            <div class="modal__actions">
                <button type="button" class="ui-btn-secondary" onclick="closeBookingModal()">Cancel</button>
                <button type="submit" class="ui-btn-primary"><i class="fas fa-check"></i> Send Request</button>
            </div>
        </form>
    </div>
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

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('bookingModal');
    const detectBtn = document.getElementById('detectLocationBtn');
    const locationInput = document.getElementById('filterLocation');
    const locationStatusText = document.getElementById('locationStatusText');
    const bookingAddressInput = document.getElementById('address');
    const fillBookingLocationBtn = document.getElementById('fillBookingLocationBtn');
    const searchLat = document.getElementById('searchLat');
    const searchLng = document.getElementById('searchLng');
    const bookingLat = document.getElementById('bookingSearchLat');
    const bookingLng = document.getElementById('bookingSearchLng');
    const serviceSearchForm = document.getElementById('serviceSearchForm');
    const defaultDetectBtnLabel = detectBtn ? detectBtn.innerHTML : '';

    async function reverseGeocodeCitySuburb(lat, lng) {
        const endpoint = 'https://api.bigdatacloud.net/data/reverse-geocode-client?latitude='
            + encodeURIComponent(lat)
            + '&longitude='
            + encodeURIComponent(lng)
            + '&localityLanguage=en';

        const response = await fetch(endpoint, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error('Reverse geocoding failed');
        }

        const data = await response.json();
        const suburb = data.locality || data.localityInfo?.administrative?.[3]?.name || '';
        const city = data.city || data.principalSubdivision || '';

        return suburb || city || '';
    }

    async function reverseGeocodeAddress(lat, lng) {
        try {
            const nominatimEndpoint = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2'
                + '&lat=' + encodeURIComponent(lat)
                + '&lon=' + encodeURIComponent(lng)
                + '&addressdetails=1';

            const nominatimResponse = await fetch(nominatimEndpoint, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });

            if (nominatimResponse.ok) {
                const nominatimData = await nominatimResponse.json();
                const addr = nominatimData.address || {};
                const line1 = [addr.house_number, addr.road].filter(Boolean).join(' ').trim();
                const suburb = addr.suburb || addr.neighbourhood || addr.residential || addr.quarter || '';
                const city = addr.city || addr.town || addr.village || addr.county || '';
                const state = addr.state || '';
                const postcode = addr.postcode || '';

                const full = [line1, suburb, city, state, postcode]
                    .filter(function (value) { return typeof value === 'string' && value.trim() !== ''; })
                    .join(', ');

                if (full) return full;
                if (typeof nominatimData.display_name === 'string' && nominatimData.display_name.trim() !== '') {
                    return nominatimData.display_name.trim();
                }
            }
        } catch (error) {
            // Fall through to BigDataCloud fallback
        }

        const fallbackEndpoint = 'https://api.bigdatacloud.net/data/reverse-geocode-client?latitude='
            + encodeURIComponent(lat)
            + '&longitude='
            + encodeURIComponent(lng)
            + '&localityLanguage=en';

        const fallbackResponse = await fetch(fallbackEndpoint, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });

        if (!fallbackResponse.ok) {
            throw new Error('Reverse geocoding failed');
        }

        const fallbackData = await fallbackResponse.json();
        const suburb = fallbackData.locality || '';
        const city = fallbackData.city || fallbackData.principalSubdivision || '';
        const postcode = fallbackData.postcode || '';
        const country = fallbackData.countryName || '';

        return [suburb, city, postcode, country]
            .filter(function (value) { return typeof value === 'string' && value.trim() !== ''; })
            .join(', ');
    }

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeBookingModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeBookingModal();
        }
    });

    if (detectBtn) {
        detectBtn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                window.uiToast('Geolocation is not supported in this browser.', 'error');
                return;
            }

            detectBtn.disabled = true;
            detectBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Locating...';

            navigator.geolocation.getCurrentPosition(async function (pos) {
                const lat = pos.coords.latitude.toFixed(7);
                const lng = pos.coords.longitude.toFixed(7);

                searchLat.value = lat;
                searchLng.value = lng;
                bookingLat.value = lat;
                bookingLng.value = lng;

                try {
                    const locationLabel = await reverseGeocodeCitySuburb(lat, lng);
                    if (locationLabel && locationInput) {
                        locationInput.value = locationLabel;
                    }

                    if (locationStatusText) {
                        locationStatusText.textContent = locationLabel
                            ? ('Location detected: ' + locationLabel)
                            : ('Using location: ' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5));
                    }
                } catch (error) {
                    if (locationStatusText) {
                        locationStatusText.textContent = 'Using location: ' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5);
                    }
                }

                detectBtn.disabled = false;
                detectBtn.innerHTML = defaultDetectBtnLabel;
                serviceSearchForm.submit();
            }, function () {
                detectBtn.disabled = false;
                detectBtn.innerHTML = defaultDetectBtnLabel;
                window.uiToast('Unable to access your current location.', 'error');
            }, { enableHighAccuracy: true, timeout: 10000 });
        });
    }

    if (fillBookingLocationBtn) {
        fillBookingLocationBtn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                window.uiToast('Geolocation is not supported in this browser.', 'error');
                return;
            }

            fillBookingLocationBtn.disabled = true;
            fillBookingLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Locating...';

            navigator.geolocation.getCurrentPosition(async function (pos) {
                const lat = pos.coords.latitude.toFixed(7);
                const lng = pos.coords.longitude.toFixed(7);

                searchLat.value = lat;
                searchLng.value = lng;
                bookingLat.value = lat;
                bookingLng.value = lng;

                try {
                    const fullAddress = await reverseGeocodeAddress(lat, lng);
                    if (fullAddress && bookingAddressInput) {
                        bookingAddressInput.value = fullAddress;
                    } else if (bookingAddressInput) {
                        const shortLocation = await reverseGeocodeCitySuburb(lat, lng);
                        bookingAddressInput.value = shortLocation || ('Lat ' + Number(lat).toFixed(5) + ', Lng ' + Number(lng).toFixed(5));
                    }
                } catch (error) {
                    if (bookingAddressInput) {
                        bookingAddressInput.value = 'Lat ' + Number(lat).toFixed(5) + ', Lng ' + Number(lng).toFixed(5);
                    }
                }

                fillBookingLocationBtn.disabled = false;
                fillBookingLocationBtn.innerHTML = '<i class="fas fa-location-crosshairs"></i> Use Current Location';
            }, function () {
                fillBookingLocationBtn.disabled = false;
                fillBookingLocationBtn.innerHTML = '<i class="fas fa-location-crosshairs"></i> Use Current Location';
                window.uiToast('Unable to access your current location.', 'error');
            }, { enableHighAccuracy: true, timeout: 10000 });
        });
    }

    // ── Location autocomplete (city/town only) ──────
    (function initLocationAutocomplete() {
        var input = document.getElementById('filterLocation');
        if (!input) return;

        var wrap = input.closest('.search-input-wrap--location');
        if (!wrap) return;

        var dropdown = document.createElement('div');
        dropdown.className = 'location-autocomplete-dropdown';
        dropdown.setAttribute('role', 'listbox');
        dropdown.setAttribute('id', 'locationSuggestions');
        dropdown.style.display = 'none';
        wrap.appendChild(dropdown);

        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-controls', 'locationSuggestions');

        var debounceTimer = null;
        var activeIndex = -1;
        var currentAbortController = null;

        var majorSouthAfricanPlaces = [
            { name: 'Johannesburg', aliases: ['Joburg', 'Jozi'] },
            { name: 'Pretoria', aliases: ['Tshwane'] },
            { name: 'Cape Town' },
            { name: 'Durban', aliases: ['eThekwini'] },
            { name: 'Gqeberha', aliases: ['Port Elizabeth', 'PE'] },
            { name: 'East London' },
            { name: 'Bloemfontein' },
            { name: 'Polokwane' },
            { name: 'Mbombela', aliases: ['Nelspruit'] },
            { name: 'Pietermaritzburg' },
            { name: 'Kimberley' },
            { name: 'Rustenburg' },
            { name: 'Mahikeng', aliases: ['Mafikeng'] },
            { name: 'Stellenbosch' },
            { name: 'George' },
            { name: 'Paarl' },
            { name: 'Worcester' },
            { name: 'Potchefstroom' },
            { name: 'Klerksdorp' },
            { name: 'Mthatha', aliases: ['Umtata'] },
            { name: 'Welkom' },
            { name: 'Soweto' },
            { name: 'Benoni' },
            { name: 'Boksburg' },
            { name: 'Vanderbijlpark' }
        ];

        function showDropdown() {
            dropdown.style.display = 'block';
            input.setAttribute('aria-expanded', 'true');
        }

        function hideDropdown() {
            dropdown.style.display = 'none';
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
            clearHighlight();
        }

        function normalize(value) {
            return String(value || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim();
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function highlightQuery(text, regex) {
            var safe = escapeHtml(text);
            if (!regex) return safe;
            return safe.replace(regex, '<mark>$1</mark>');
        }

        function clearHighlight() {
            var items = dropdown.querySelectorAll('.location-autocomplete-item');
            items.forEach(function (item) {
                item.classList.remove('location-autocomplete-item--active');
                item.setAttribute('aria-selected', 'false');
            });
        }

        function highlightItem(index) {
            var items = dropdown.querySelectorAll('.location-autocomplete-item');
            if (items.length === 0) return;
            clearHighlight();
            if (index < 0) index = items.length - 1;
            if (index >= items.length) index = 0;
            activeIndex = index;
            items[activeIndex].classList.add('location-autocomplete-item--active');
            items[activeIndex].setAttribute('aria-selected', 'true');
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        }

        function selectItem(label) {
            input.value = label;
            hideDropdown();
            input.focus();
        }

        function formatResult(result) {
            if (result.__formattedLabel) {
                return {
                    primary: result.__formattedPrimary,
                    label: result.__formattedLabel,
                };
            }

            var addr = result.address || {};
            var primary = addr.city || addr.town || result.name || '';

            if (!primary && typeof result.display_name === 'string') {
                primary = result.display_name.split(',')[0].trim();
            }

            return {
                primary: primary,
                label: primary,
            };
        }

        function baseMatchScore(value, queryNorm) {
            var target = normalize(value);
            if (!target || !queryNorm) return 0;
            if (target === queryNorm) return 1000;
            if (target.startsWith(queryNorm)) return 800;
            if (target.indexOf(' ' + queryNorm) !== -1) return 650;
            if (target.indexOf(queryNorm) !== -1) return 500;
            return 0;
        }

        function scoreSuggestion(result, query) {
            var queryNorm = normalize(query);
            var formatted = formatResult(result);
            var score = 0;

            score = Math.max(score, baseMatchScore(formatted.primary, queryNorm));

            if (result.source === 'major') score += 180;

            var type = normalize(result.type || result.addresstype);
            if (type === 'city') score += 90;
            if (type === 'town') score += 80;

            var importance = Number(result.importance || 0);
            if (Number.isFinite(importance)) {
                score += Math.round(importance * 40);
            }

            return score;
        }

        function toMajorPlaceSuggestion(place) {
            return {
                source: 'major',
                type: 'city',
                importance: 1,
                __formattedPrimary: place.name,
                __formattedLabel: place.name,
            };
        }

        function getMajorPlaceSuggestions(query) {
            var queryNorm = normalize(query);
            if (queryNorm.length < 2) return [];

            return majorSouthAfricanPlaces
                .map(function (place) {
                    var aliasScores = (place.aliases || []).map(function (alias) {
                        return baseMatchScore(alias, queryNorm);
                    });
                    var score = Math.max(baseMatchScore(place.name, queryNorm), ...aliasScores);
                    return { place: place, score: score };
                })
                .filter(function (row) { return row.score > 0; })
                .sort(function (a, b) {
                    if (b.score !== a.score) return b.score - a.score;
                    return a.place.name.localeCompare(b.place.name);
                })
                .slice(0, 8)
                .map(function (row) { return toMajorPlaceSuggestion(row.place); });
        }

        function isCityTownResult(result) {
            var addr = result.address || {};
            if (addr.city || addr.town) return true;

            var type = normalize(result.type || result.addresstype);
            return type === 'city' || type === 'town';
        }

        function dedupeAndRank(results, query, limit) {
            var seen = {};

            return results
                .map(function (result) {
                    var formatted = formatResult(result);
                    return {
                        result: result,
                        formatted: formatted,
                        score: scoreSuggestion(result, query),
                    };
                })
                .filter(function (row) {
                    return row.formatted.primary && row.score > 0;
                })
                .sort(function (a, b) {
                    if (b.score !== a.score) return b.score - a.score;
                    return a.formatted.label.localeCompare(b.formatted.label);
                })
                .filter(function (row) {
                    var key = normalize(row.formatted.label);
                    if (seen[key]) return false;
                    seen[key] = true;
                    return true;
                })
                .slice(0, limit || 8)
                .map(function (row) { return row.result; });
        }

        function renderDropdown(results, query) {
            dropdown.innerHTML = '';

            if (results.length === 0) {
                dropdown.innerHTML =
                    '<div class="location-autocomplete-empty">'
                    + '<i class="fas fa-map-marker-alt"></i> No locations found'
                    + '</div>';
                showDropdown();
                return;
            }

            var escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            var regex = escapedQuery ? new RegExp('(' + escapedQuery + ')', 'gi') : null;

            var unique = dedupeAndRank(results, query, 8);

            unique.forEach(function (result, i) {
                var formatted = formatResult(result);
                var item = document.createElement('div');
                item.className = 'location-autocomplete-item';
                item.setAttribute('role', 'option');
                item.setAttribute('aria-selected', 'false');
                item.setAttribute('data-index', i);
                item.setAttribute('data-label', formatted.label);

                var highlightedPrimary = highlightQuery(formatted.primary, regex);

                item.innerHTML =
                    '<i class="fas fa-map-marker-alt"></i>'
                    + '<div class="location-autocomplete-text">'
                    + '<span>' + highlightedPrimary + '</span>'
                    + '</div>';

                item.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    selectItem(formatted.label);
                });

                item.addEventListener('mouseenter', function () {
                    activeIndex = i;
                    clearHighlight();
                    item.classList.add('location-autocomplete-item--active');
                });

                dropdown.appendChild(item);
            });

            activeIndex = -1;
            showDropdown();
        }

        function renderLoading() {
            dropdown.innerHTML =
                '<div class="location-autocomplete-loading">'
                + '<i class="fas fa-spinner fa-spin"></i> Searching...'
                + '</div>';
            showDropdown();
        }

        async function fetchNominatimSuggestions(query, signal) {
            try {
                var url = 'https://nominatim.openstreetmap.org/search'
                    + '?q=' + encodeURIComponent(query)
                    + '&countrycodes=za'
                    + '&format=jsonv2'
                    + '&addressdetails=1'
                    + '&accept-language=en'
                    + '&limit=12';

                var response = await fetch(url, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    signal: signal,
                });

                if (!response.ok) return [];

                var results = await response.json();
                if (!Array.isArray(results)) return [];

                return results.filter(isCityTownResult);
            } catch (err) {
                if (err && err.name === 'AbortError') throw err;
                return [];
            }
        }

        async function fetchSuggestions(query) {
            if (currentAbortController) {
                currentAbortController.abort();
            }
            currentAbortController = new AbortController();

            var localMajorSuggestions = getMajorPlaceSuggestions(query);
            if (localMajorSuggestions.length > 0) {
                renderDropdown(localMajorSuggestions, query);
            } else {
                renderLoading();
            }

            try {
                var signal = currentAbortController.signal;
                var nominatimSuggestions = await fetchNominatimSuggestions(query, signal);

                var combined = dedupeAndRank(
                    localMajorSuggestions.concat(nominatimSuggestions || []),
                    query,
                    8
                );

                if (combined.length === 0 && localMajorSuggestions.length > 0) {
                    renderDropdown(localMajorSuggestions, query);
                    return;
                }

                if (combined.length === 0) {
                    hideDropdown();
                    return;
                }

                renderDropdown(combined, query);
            } catch (err) {
                if (err && err.name === 'AbortError') return;
                if (localMajorSuggestions.length > 0) {
                    renderDropdown(localMajorSuggestions, query);
                } else {
                    hideDropdown();
                }
            }
        }

        input.addEventListener('input', function () {
            var query = input.value.trim();

            clearTimeout(debounceTimer);

            if (query.length < 2) {
                hideDropdown();
                return;
            }

            debounceTimer = setTimeout(function () {
                fetchSuggestions(query);
            }, 250);
        });

        input.addEventListener('keydown', function (e) {
            var items = dropdown.querySelectorAll('.location-autocomplete-item');
            if (items.length === 0 && e.key !== 'Escape') return;

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    highlightItem(activeIndex + 1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    highlightItem(activeIndex - 1);
                    break;
                case 'Enter':
                    if (activeIndex >= 0 && items[activeIndex]) {
                        e.preventDefault();
                        selectItem(items[activeIndex].getAttribute('data-label'));
                    }
                    break;
                case 'Escape':
                    hideDropdown();
                    break;
                case 'Tab':
                    hideDropdown();
                    break;
            }
        });

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) {
                hideDropdown();
            }
        });

        var form = document.getElementById('serviceSearchForm');
        if (form) {
            form.addEventListener('submit', function () {
                hideDropdown();
            });
        }

        input.addEventListener('focus', function () {
            if (dropdown.children.length > 0 && input.value.trim().length >= 2) {
                showDropdown();
            }
        });
    })();
});
</script>
@endpush
