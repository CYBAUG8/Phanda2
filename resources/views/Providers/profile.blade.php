@extends('Providers.layout')

@section('content')
<<<<<<< HEAD
<div class="p-6 space-y-8" 
     x-data="providerProfile({{ json_encode($provider) }})" 
     x-init="init()">
=======
<div x-data="providerProfile(@js($provider))" x-init="init()" class="provider-page-shell space-y-6">
    <section class="provider-page-header">
        <div>
            <h1>Profile</h1>
            <p class="provider-page-subtitle">Keep your business details accurate so customers can trust and book your services.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('provider.services.index') }}" class="ui-btn-secondary">
                <i class="fa-solid fa-concierge-bell"></i>
                <span>Manage Services</span>
            </a>
            <button type="button" @click="openEditModal = true" class="ui-btn-primary">
                <i class="fa-solid fa-pen"></i>
                <span>Edit Profile</span>
            </button>
        </div>
    </section>
>>>>>>> feature2

    @include('partials.ui.flash')

    <section class="ui-card p-5 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-orange-400 to-orange-600 text-2xl font-bold text-white">
                <span x-text="getInitials()"></span>
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="truncate text-2xl font-bold text-slate-900" x-text="profile.business_name || profile.full_name || 'Provider'"></h2>
                    <span class="provider-status-badge provider-status-confirmed">Provider</span>
                </div>
                <p class="mt-1 truncate text-sm text-slate-600" x-text="profile.email || 'No email available'"></p>
                <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                    <span class="inline-flex items-center gap-1 font-semibold text-amber-600">
                        <i class="fa-solid fa-star"></i>
                        <span x-text="Number(profile.rating_avg || 0).toFixed(1)"></span>
                    </span>
                    <span class="text-slate-500" x-text="(profile.services || []).length + ' services listed'"></span>
                </div>
            </div>
        </div>
    </section>

    <section class="provider-metrics-grid" aria-label="Profile summary metrics">
        <article class="provider-metric-card">
            <p class="provider-metric-label">Total Bookings</p>
            <p class="provider-metric-value" x-text="number(profile.total_bookings)"></p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Active Jobs</p>
            <p class="provider-metric-value text-indigo-700" x-text="number(profile.active_jobs)"></p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Completed</p>
            <p class="provider-metric-value text-emerald-700" x-text="number(profile.completed_jobs)"></p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Total Earnings</p>
            <p class="provider-metric-value text-orange-700" x-text="'R' + money(profile.total_earnings)"></p>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <article class="provider-section-card xl:col-span-2">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="provider-section-title">Business Information</h2>
                    <p class="provider-section-copy">Customers use this information when deciding whether to book.</p>
                </div>
                <button type="button" @click="openEditModal = true" class="ui-btn-secondary px-3 py-2 text-xs">
                    <i class="fa-solid fa-pen"></i>
                    <span>Edit</span>
                </button>
            </div>

            <div class="provider-kv-grid">
                <div class="provider-kv-item">
                    <p class="provider-kv-label">Business Name</p>
                    <p class="provider-kv-value" x-text="profile.business_name || 'Not set'"></p>
                </div>
                <div class="provider-kv-item">
                    <p class="provider-kv-label">Phone</p>
                    <p class="provider-kv-value" x-text="profile.phone || 'Not set'"></p>
                </div>
                <div class="provider-kv-item">
                    <p class="provider-kv-label">Service Area</p>
                    <p class="provider-kv-value" x-text="profile.service_area || 'Not set'"></p>
                </div>
                <div class="provider-kv-item">
                    <p class="provider-kv-label">Service Radius</p>
                    <p class="provider-kv-value" x-text="profile.service_radius_km ? profile.service_radius_km + ' km' : 'Not set'"></p>
                </div>
                <div class="provider-kv-item">
                    <p class="provider-kv-label">Years of Experience</p>
                    <p class="provider-kv-value" x-text="profile.years_experience ? profile.years_experience + ' years' : 'Not set'"></p>
                </div>
            </div>

            <div x-show="!profile.service_area" class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                Add your service area so customers can discover your services and you can create listings.
            </div>
        </article>

        <article class="provider-section-card">
            <h2 class="provider-section-title">Account Status</h2>
            <p class="provider-section-copy">Verification status for your provider account.</p>

            <div class="mt-4 space-y-3">
                <p class="provider-status-dot" :class="statusColor()" x-text="statusLabel()"></p>
                <p class="text-xs text-slate-500" x-text="statusCopy()"></p>
            </div>
        </article>
    </section>

    <section class="provider-section-card">
        <h2 class="provider-section-title">Services Offered</h2>
        <p class="provider-section-copy">These services appear on your provider listings.</p>

        <div class="mt-4 flex flex-wrap gap-2">
            <template x-for="service in profile.services || []" :key="service.service_id">
                <span class="provider-status-badge provider-status-paused">
                    <span x-text="service.title || 'Service'"></span>
                </span>
            </template>
            <template x-if="!profile.services || profile.services.length === 0">
                <div class="provider-empty-inline w-full">
                    No services added yet. Add your first service to start receiving bookings.
                </div>
            </template>
        </div>
    </section>

    <div
        x-show="openEditModal"
        x-cloak
        x-on:keydown.escape.window="closeModal()"
        class="fixed inset-0 z-50 overflow-y-auto p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="profileEditTitle"
    >
        <div class="flex min-h-full items-center justify-center">
            <div class="fixed inset-0 bg-slate-950/50" @click="closeModal()"></div>
            <div class="provider-modal-panel relative z-10 w-full max-w-xl">
                <div class="provider-modal-header">
                    <div>
                        <h3 id="profileEditTitle" class="text-lg font-semibold text-slate-900">Edit Business Information</h3>
                        <p class="mt-1 text-xs text-slate-500">Update your details to keep your profile accurate.</p>
                    </div>
                    <button type="button" class="text-slate-500 hover:text-slate-700" @click="closeModal()" :disabled="saving" aria-label="Close profile edit modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form @submit.prevent="updateProfile" class="provider-modal-body space-y-4">
                    <div>
                        <label for="businessName" class="provider-label normal-case tracking-normal text-slate-700">Business Name</label>
                        <input id="businessName" type="text" x-model="editForm.business_name" class="provider-input" required>
                    </div>

                    <div>
                        <label for="phone" class="provider-label normal-case tracking-normal text-slate-700">Phone</label>
                        <input id="phone" type="text" x-model="editForm.phone" class="provider-input" required>
                    </div>

                    <div>
                        <label for="serviceAreaInput" class="provider-label normal-case tracking-normal text-slate-700">Service Area</label>
                        <div class="relative" @click.outside="locationResults = []">
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <input
                                    id="serviceAreaInput"
                                    type="text"
                                    x-model="editForm.service_area"
                                    @input="onLocationInput($event)"
                                    @focus="onLocationFocus()"
                                    autocomplete="off"
                                    class="provider-input sm:flex-1"
                                    placeholder="Search your service location"
                                >
                                <button
                                    type="button"
                                    class="ui-btn-secondary px-3 py-2 text-xs sm:text-sm disabled:cursor-not-allowed disabled:opacity-60"
                                    @click="useCurrentLocationForServiceArea()"
                                    :disabled="locatingCurrentLocation || saving"
                                >
                                    <i class="fa-solid" :class="locatingCurrentLocation ? 'fa-spinner animate-spin' : 'fa-location-crosshairs'"></i>
                                    <span x-text="locatingCurrentLocation ? 'Locating...' : 'Use Current'"></span>
                                </button>
                            </div>
                            <ul
                                x-show="locationSearchLoading || locationResults.length > 0 || showNoLocationResults()"
                                x-cloak
                                class="absolute z-50 mt-1 max-h-56 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg"
                            >
                                <li
                                    x-show="locationSearchLoading"
                                    class="px-3 py-2 text-sm text-slate-500"
                                >
                                    Searching locations...
                                </li>
                                <template x-for="(place, index) in locationResults" :key="index">
                                    <li
                                        class="cursor-pointer px-3 py-2 text-sm text-slate-700 hover:bg-orange-50"
                                        x-text="place.display_name"
                                        @mousedown.prevent="selectLocation(place)"
                                    ></li>
                                </template>
                                <li
                                    x-show="showNoLocationResults()"
                                    class="px-3 py-2 text-sm text-slate-500"
                                >
                                    No locations found. Try a nearby suburb or city.
                                </li>
                            </ul>
                        </div>
                        <p class="mt-2 text-xs text-slate-500" x-show="editForm.last_lat && editForm.last_lng">
                            Coordinates: <span x-text="editForm.last_lat"></span>, <span x-text="editForm.last_lng"></span>
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="radius" class="provider-label normal-case tracking-normal text-slate-700">Service Radius (km)</label>
                            <input id="radius" type="number" x-model="editForm.service_radius_km" class="provider-input" min="1" max="100" placeholder="10">
                        </div>
                        <div>
                            <label for="experience" class="provider-label normal-case tracking-normal text-slate-700">Years of Experience</label>
                            <input id="experience" type="number" x-model="editForm.years_experience" class="provider-input" min="0" placeholder="0">
                        </div>
                    </div>

                    <p x-show="formError" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700" x-text="formError"></p>
                </form>

                <div class="provider-modal-footer">
                    <button type="button" @click="closeModal()" class="ui-btn-secondary px-4 py-2" :disabled="saving">Cancel</button>
                    <button type="button" @click="updateProfile()" class="ui-btn-primary px-4 py-2 disabled:cursor-not-allowed disabled:opacity-60" :disabled="saving">
                        <i x-show="saving" class="fa-solid fa-spinner animate-spin"></i>
                        <span x-text="saving ? 'Saving...' : 'Save Changes'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function providerProfile(initialData) {
    return {
        profile: initialData || {},
        openEditModal: false,
        saving: false,
        formError: '',
        locationResults: [],
        locationSearchLoading: false,
        locatingCurrentLocation: false,
        locationQuery: '',
        locationRequestId: 0,
        searchTimeout: null,
        editForm: {
            business_name: '',
            phone: '',
            service_area: '',
            service_radius_km: '',
            last_lat: '',
            last_lng: '',
            years_experience: '',
        },
        init() {
            this.$watch('openEditModal', (isOpen) => {
                if (isOpen) {
                    this.formError = '';
                    this.locationResults = [];
                    this.locationSearchLoading = false;
                    this.locatingCurrentLocation = false;
                    this.locationQuery = '';
                    this.editForm = {
                        business_name: this.profile.business_name || '',
                        phone: this.profile.phone || '',
                        service_area: this.profile.service_area || '',
                        service_radius_km: this.profile.service_radius_km || '',
                        last_lat: this.profile.last_lat || '',
                        last_lng: this.profile.last_lng || '',
                        years_experience: this.profile.years_experience || '',
                    };
                } else {
                    this.locationResults = [];
                    this.locationSearchLoading = false;
                    this.locatingCurrentLocation = false;
                    this.locationQuery = '';
                }
            });
        },
        number(value) {
            return Number(value || 0).toLocaleString();
        },
        money(value) {
            return Number(value || 0).toFixed(2);
        },
        getInitials() {
            const source = this.profile.business_name || this.profile.full_name || '';
            if (!source) {
                return 'PR';
            }
            return source
                .split(' ')
                .filter(Boolean)
                .slice(0, 2)
                .map((part) => part.substring(0, 1).toUpperCase())
                .join('');
        },
        statusLabel() {
            const status = String(this.profile.kyc_status || 'PENDING').toUpperCase();
            if (status === 'APPROVED') {
                return 'Verified';
            }
            if (status === 'REJECTED') {
                return 'Requires Attention';
            }
            return 'Pending Verification';
        },
        statusColor() {
            const status = String(this.profile.kyc_status || 'PENDING').toUpperCase();
            if (status === 'APPROVED') {
                return 'text-emerald-700';
            }
            if (status === 'REJECTED') {
                return 'text-rose-700';
            }
            return 'text-amber-700';
        },
        statusCopy() {
            const status = String(this.profile.kyc_status || 'PENDING').toUpperCase();
            if (status === 'APPROVED') {
                return 'Your account is verified and can accept new bookings.';
            }
            if (status === 'REJECTED') {
                return 'Your verification needs updates. Review your details and contact support if needed.';
            }
            return 'Your details are under review. Keep your profile complete while verification is pending.';
        },
        onLocationInput(event) {
            const query = String(event.target.value || '').trim();
            clearTimeout(this.searchTimeout);
            this.locationQuery = query;
            if (query.length < 3) {
                this.locationResults = [];
                this.locationSearchLoading = false;
                return;
            }

            this.searchTimeout = setTimeout(() => this.fetchLocations(query), 350);
        },
        onLocationFocus() {
            const query = String(this.editForm.service_area || '').trim();
            this.locationQuery = query;
            if (query.length >= 3 && this.locationResults.length === 0) {
                this.fetchLocations(query);
            }
        },
        showNoLocationResults() {
            return !this.locationSearchLoading && this.locationResults.length === 0 && this.locationQuery.length >= 3;
        },
        async fetchLocations(query) {
            const requestId = ++this.locationRequestId;
            this.locationSearchLoading = true;
            try {
                const response = await fetch(`/api/places/search?q=${encodeURIComponent(query)}`);
                const payload = await response.json();
                if (requestId !== this.locationRequestId) {
                    return;
                }
                this.locationResults = Array.isArray(payload) ? payload : [];
            } catch (error) {
                console.error('Location search error:', error);
                if (requestId === this.locationRequestId) {
                    this.locationResults = [];
                }
            } finally {
                if (requestId === this.locationRequestId) {
                    this.locationSearchLoading = false;
                }
            }
        },
        selectLocation(place) {
            this.editForm.service_area = place.display_name;
            this.editForm.last_lat = place.lat;
            this.editForm.last_lng = place.lon;
            this.locationQuery = this.editForm.service_area;
            this.locationResults = [];
        },
        useCurrentLocationForServiceArea() {
            if (!navigator.geolocation) {
                window.uiToast('Geolocation is not supported in this browser.', 'error');
                return;
            }

            if (this.locatingCurrentLocation || this.saving) {
                return;
            }

            clearTimeout(this.searchTimeout);
            this.locationResults = [];
            this.locationSearchLoading = false;
            this.locatingCurrentLocation = true;

            navigator.geolocation.getCurrentPosition(async (position) => {
                const lat = Number(position.coords.latitude.toFixed(7));
                const lng = Number(position.coords.longitude.toFixed(7));
                this.editForm.last_lat = lat;
                this.editForm.last_lng = lng;

                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`);
                    if (!response.ok) {
                        throw new Error('Reverse geocode request failed');
                    }

                    const payload = await response.json();
                    const address = payload && payload.address ? payload.address : {};
                    const areaParts = [
                        address.suburb || address.neighbourhood || address.city_district,
                        address.city || address.town || address.village || address.county,
                        address.state,
                    ].filter((part, index, items) => part && items.indexOf(part) === index);

                    const areaLabel = areaParts.join(', ').trim();
                    this.editForm.service_area = areaLabel || payload.display_name || `${lat}, ${lng}`;
                    this.locationQuery = this.editForm.service_area;
                    window.uiToast('Service area filled from your current location.', 'success');
                } catch (error) {
                    console.error('Reverse geocode error:', error);
                    this.editForm.service_area = `${lat}, ${lng}`;
                    this.locationQuery = this.editForm.service_area;
                    window.uiToast('Location captured. You can refine the service area text.', 'success');
                } finally {
                    this.locatingCurrentLocation = false;
                }
            }, (error) => {
                this.locatingCurrentLocation = false;
                const denied = error && error.code === error.PERMISSION_DENIED;
                window.uiToast(denied ? 'Location permission denied. Allow access and try again.' : 'Unable to detect your current location.', 'error');
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000,
            });
        },
        closeModal() {
            if (this.saving) {
                return;
            }
            this.openEditModal = false;
        },
        async updateProfile() {
            if (this.saving) {
                return;
            }

            this.saving = true;
            this.formError = '';

            try {
                const response = await fetch('{{ route('provider.profile.update') }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.editForm),
                });

                const payload = await response.json();
                if (!response.ok) {
                    if (response.status === 422 && payload.errors) {
                        const firstError = Object.values(payload.errors)[0];
                        this.formError = Array.isArray(firstError) ? firstError[0] : 'Validation failed.';
                    } else {
                        this.formError = payload.message || 'Unable to update profile right now.';
                    }
                    return;
                }

                this.profile = { ...this.profile, ...payload };
                this.openEditModal = false;
                window.uiToast('Profile updated successfully.', 'success');
            } catch (error) {
                console.error('Profile update error:', error);
                this.formError = 'Network error. Please try again.';
            } finally {
                this.saving = false;
            }
        },
    };
}
</script>
@endpush
