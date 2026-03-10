@extends('Users.layout')

@section('content')
<div class="page-header">
    <h2>Find Services</h2>
    <p>Find nearby providers and send requests quickly.</p>
</div>

@if(session('success'))
    <div class="flash-message flash-message--success">
        <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="flash-message flash-message--error">
        <i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span>
    </div>
@endif

@if(!empty($showProximityWarning))
    <div class="flash-message flash-message--error">
        <i class="fas fa-location-crosshairs"></i>
        <span>Set your location to see providers within your radius.</span>
    </div>
@endif

<form action="{{ route('users.services') }}" method="GET" class="search-section card" id="serviceSearchForm">
    <input type="hidden" name="lat" id="searchLat" value="{{ $filters['lat'] ?? '' }}">
    <input type="hidden" name="lng" id="searchLng" value="{{ $filters['lng'] ?? '' }}">

    <div class="search-row">
        <div class="search-input-wrap">
            <i class="fas fa-search search-icon"></i>
            <input type="text" name="search" class="search-input" placeholder="Search services or providers" value="{{ $filters['search'] }}">
        </div>

        <div class="search-input-wrap search-input-wrap--location">
            <i class="fas fa-map-marker-alt search-icon"></i>
            <input type="text" name="location" id="filterLocation" class="search-input" placeholder="Location (city or suburb)" value="{{ $filters['location'] }}">
        </div>

        <button type="submit" class="btn-primary">
            <i class="fas fa-search"></i>
            <span>Search</span>
        </button>
    </div>

    <div class="search-row" style="margin-top: 12px; grid-template-columns: 180px auto 1fr;">
        <div>
            <label for="radius_km" class="form-label">Radius (km)</label>
            <input type="number" min="1" max="100" class="form-input" name="radius_km" id="radius_km" value="{{ $filters['radius_km'] ?? 25 }}">
        </div>

        <div style="display:flex;align-items:end;">
            <button type="button" class="btn-outline" id="detectLocationBtn">
                <i class="fas fa-location-crosshairs"></i> Use Current Location
            </button>
        </div>

        <div style="display:none;">
            <small class="text-muted" id="locationStatusText" aria-live="polite"></small>
        </div>
    </div>
</form>

<div class="category-pills-container" style="margin-bottom: 24px; width: 100%;">
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

<div class="results-header" style="margin-bottom: 24px;">
    <span class="results-count">Showing <strong>{{ $services->total() }}</strong> services</span>
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
            <div class="service-card">
                <div class="service-card__image">
                    <i class="fas {{ optional($service->category)->icon ?? 'fa-concierge-bell' }}"></i>
                    <span class="service-card__category-badge">{{ optional($service->category)->name ?? 'General' }}</span>
                </div>

                <div class="service-card__body">
                    <h3 class="service-card__title">{{ $service->title }}</h3>
                    <p class="service-card__provider"><i class="fas fa-user-circle"></i> {{ $service->provider_name }}</p>
                    <p class="service-card__description">{{ \Illuminate\Support\Str::limit($service->description, 90) }}</p>
                </div>

                <div class="service-card__footer">
                    <div class="service-card__meta">
                        <span class="service-card__price">R{{ number_format((float) $service->base_price, 2) }}</span>
                        <span class="service-card__duration"><i class="far fa-clock"></i> {{ $service->formatted_duration }}</span>
                    </div>

                    <div class="service-card__location">
                        <i class="fas fa-map-marker-alt"></i>
                        @if(isset($service->distance_km))
                            {{ number_format((float) $service->distance_km, 1) }} km away
                        @else
                            {{ $service->location }}
                        @endif
                    </div>

                    <button type="button" class="btn-primary" onclick='openBookingModal(@json($service->service_id), @json($service->title), @json($service->formatted_price), @json($service->provider_name))'>
                        <i class="fas fa-calendar-alt"></i> Request Service
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pagination-wrap">{{ $services->links() }}</div>
@else
    <div class="empty-state card">
        <div class="empty-state__icon"><i class="fas fa-search"></i></div>
        <h3>No nearby services found</h3>
        <p>Try increasing your radius or updating your location.</p>
        <a href="{{ route('users.services') }}" class="btn-primary"><i class="fas fa-redo"></i> Reset</a>
    </div>
@endif

<div class="modal-overlay" id="bookingModal" aria-hidden="true">
    <div class="booking-modal">
        <div class="modal__header">
            <h3><i class="fas fa-calendar-check"></i> Send Service Request</h3>
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

            <div class="grid modal__grid-fix" style="grid-template-columns: 1fr 1fr; margin-bottom: 20px; gap: 16px;">
                <div class="form-group">
                    <label for="booking_date"><i class="far fa-calendar"></i> Service Date</label>
                    <input type="date" name="booking_date" id="booking_date" class="form-input" min="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label for="start_time"><i class="far fa-clock"></i> Start Time</label>
                    <input type="time" name="start_time" id="start_time" class="form-input" min="07:00" max="18:00" required>
                </div>
            </div>

            <div class="form-group">
                <label for="address"><i class="fas fa-map-marker-alt"></i> Full Address</label>
                <input type="text" name="address" id="address" class="form-input" placeholder="Street, suburb, city" required>
                <button type="button" class="btn-outline" style="margin-top:8px;" id="fillBookingLocationBtn">
                    <i class="fas fa-location-crosshairs"></i> Use Current Location
                </button>
            </div>

            <div class="form-group">
                <label for="notes"><i class="fas fa-sticky-note"></i> Notes</label>
                <textarea name="notes" id="notes" class="form-input form-textarea" rows="3" placeholder="Any special details"></textarea>
            </div>

            <div class="modal__actions">
                <button type="button" class="btn-outline" onclick="closeBookingModal()">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Send Request</button>
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
                alert('Geolocation is not supported in this browser.');
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
                alert('Unable to access your current location.');
            }, { enableHighAccuracy: true, timeout: 10000 });
        });
    }

    if (fillBookingLocationBtn) {
        fillBookingLocationBtn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported in this browser.');
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
                alert('Unable to access your current location.');
            }, { enableHighAccuracy: true, timeout: 10000 });
        });
    }
});
</script>
@endpush