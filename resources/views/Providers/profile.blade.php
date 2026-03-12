@extends('Providers.layout')

@section('content')
<div class="p-6 space-y-8" 
     x-data="providerProfile({{ json_encode($provider) }})" 
     x-init="init()">

    <!-- PROFILE HEADER -->
    <section class="bg-white border rounded-lg p-8 shadow-sm">
        <div class="flex items-center gap-6">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white text-3xl font-bold shadow">
                <span x-text="getInitials()"></span>
            </div>

            <div class="flex-1">
                <span class="px-3 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full">
                    Service Provider
                </span>

                <h1 class="text-3xl font-bold mt-2" x-text="profile.business_name"></h1>
                <p class="text-gray-600" x-text="profile.email"></p>

                <div class="flex items-center gap-2 mt-2">
                    <span class="text-yellow-500">★</span>
                    <span class="font-medium" x-text="profile.rating_avg || '0.0'"></span>
                    <span class="text-gray-500 text-sm">({{ count($provider['services'] ?? []) }} services)</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ACTION BUTTONS -->
    <div class="flex justify-end">
        <a href="/providers/services" 
           class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition">
            Manage Services
        </a>
    </div>

    <!-- ACCOUNT SUMMARY -->
    <section class="bg-white border rounded-lg p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-orange-500 mb-4">Account Summary</h2>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-4 border rounded-lg">
                <div class="text-2xl font-bold text-orange-500" x-text="profile.total_bookings || 0"></div>
                <div class="text-gray-600 text-sm">Total Bookings</div>
            </div>
            <div class="text-center p-4 border rounded-lg">
                <div class="text-2xl font-bold text-orange-500" x-text="profile.active_jobs || 0"></div>
                <div class="text-gray-600 text-sm">Active Jobs</div>
            </div>
            <div class="text-center p-4 border rounded-lg">
                <div class="text-2xl font-bold text-orange-500" x-text="profile.completed_jobs || 0"></div>
                <div class="text-gray-600 text-sm">Completed</div>
            </div>
            <div class="text-center p-4 border rounded-lg">
                <div class="text-2xl font-bold text-green-600" x-text="'R ' + (profile.total_earnings || 0)"></div>
                <div class="text-gray-600 text-sm">Total Earnings</div>
            </div>
        </div>
    </section>

    <!-- BUSINESS INFORMATION -->
    <section class="bg-white border rounded-lg p-6 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-orange-500">Business Information</h2>
            <button @click="openEditModal = true" class="text-orange-500 hover:text-orange-600 text-sm font-medium">
                ✎ Edit
            </button>
        </div>

        <div class="space-y-3 text-gray-700">
            <div class="flex justify-between border-b pb-3">
                <span class="font-medium">Business Name:</span>
                <span x-text="profile.business_name"></span>
            </div>
            <div class="flex justify-between border-b pb-3">
                <span class="font-medium">Phone:</span>
                <span x-text="profile.phone"></span>
            </div>
            <div class="flex justify-between border-b pb-3">
                <span class="font-medium">Service Area:</span>
                <span x-text="profile.service_area"></span>
            </div>
            <div class="flex justify-between border-b pb-3">
                <span class="font-medium">Years of Experience:</span>
                <span x-text="profile.years_experience + ' years'"></span>
            </div>
        </div>
    </section>

    <!-- SERVICES OFFERED -->
    <section class="bg-white border rounded-lg p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-orange-500 mb-4">Services Offered</h2>

        <div class="flex flex-wrap gap-2">
            <template x-for="service in profile.services" :key="service.service_id">
                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">
                    <span x-text="service.title"></span>
                </span>
            </template>
            <template x-if="(!profile.services || profile.services.length === 0)">
                <span class="text-gray-500">No services added yet.</span>
            </template>
        </div>
    </section>

    <!-- ACCOUNT STATUS -->
    <section class="bg-white border rounded-lg p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-orange-500 mb-4">Account Status</h2>

        <div class="flex items-center gap-3">
            <div class="w-3 h-3 rounded-full"
                 :class="profile.kyc_status === 'APPROVED' ? 'bg-green-500' : 'bg-yellow-500'">
            </div>
            <span class="capitalize font-medium" x-text="profile.kyc_status"></span>
        </div>
    </section>

    <!-- EDIT PROFILE MODAL -->
    <div x-show="openEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75"
         @click.self="openEditModal = false">
        
        <div x-transition
             class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6"
             @click.stop>
             
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Business Information</h3>
                <button type="button" @click="openEditModal = false" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>

            <form @submit.prevent="updateProfile" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Business Name</label>
                    <input type="text" x-model="editForm.business_name" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" x-model="editForm.phone" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>

                <!-- Service Area with autocomplete -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Service Area</label>
                    <!-- wrapper needs position:relative so dropdown is anchored to it -->
                    <div class="relative">
                        <input 
                            id="serviceAreaInput"
                            type="text"
                            x-model="editForm.service_area"
                            @input="onLocationInput($event)"
                            autocomplete="off"
                            class="mt-1 block w-full border-gray-300 rounded-md"
                            placeholder="Search your service location"
                        >
                        <!-- Dropdown renders here, anchored under input -->
                        <ul 
                            x-show="locationResults.length > 0"
                            x-cloak
                            class="absolute z-50 bg-white border rounded-md shadow w-full mt-1 max-h-48 overflow-y-auto"
                        >
                            <template x-for="(place, index) in locationResults" :key="index">
                                <li 
                                    class="px-4 py-2 hover:bg-orange-50 cursor-pointer text-sm"
                                    x-text="place.display_name"
                                    @mousedown.prevent="selectLocation(place)"
                                ></li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Service Radius (km)</label>
                    <input type="number" x-model="editForm.service_radius_km" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="10">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Years of Experience</label>
                    <input type="number" x-model="editForm.years_experience" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2 bg-gray-200 rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-md">Save</button>
                </div>
            </form>
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
        locationResults: [],
        searchTimeout: null,

        editForm: {
            business_name: '',
            phone: '',
            service_area: '',
            service_radius_km: '',
            last_lat: '',
            last_lng: '',
            years_experience: ''
        },

        init() {
            this.$watch('openEditModal', (value) => {
                if (value) {
                    // Populate form with current profile data when modal opens
                    this.editForm = {
                        business_name: this.profile.business_name || '',
                        phone:         this.profile.phone || '',
                        service_area:  this.profile.service_area || '',
                        service_radius_km: this.profile.service_radius_km || '',
                        last_lat:      this.profile.last_lat || '',
                        last_lng:      this.profile.last_lng || '',
                        years_experience: this.profile.years_experience || ''
                    };
                } else {
                    // Clear dropdown when modal closes
                    this.locationResults = [];
                }
            });
        },

        getInitials() {
            if (!this.profile.business_name) return '';
            return this.profile.business_name
                .split(' ')
                .map(n => n[0])
                .join('')
                .toUpperCase()
                .substring(0, 2);
        },

        onLocationInput(e) {
            const query = e.target.value;

            // Keep x-model in sync (it already is via x-model, but clear results if too short)
            if (query.length < 3) {
                this.locationResults = [];
                return;
            }

            // Debounce manually — do NOT also use Alpine's .debounce modifier
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => this.fetchLocations(query), 400);
        },

        async fetchLocations(query) {
            try {
                const res = await fetch(`/api/places/search?q=${encodeURIComponent(query)}`);
                const data = await res.json();
                this.locationResults = Array.isArray(data) ? data : [];
            } catch (err) {
                console.error('Location search error:', err);
                this.locationResults = [];
            }
        },

        selectLocation(place) {
            // This fires on mousedown.prevent so the input never loses focus first
            this.editForm.service_area = place.display_name;
            this.editForm.last_lat     = place.lat;
            this.editForm.last_lng     = place.lon;
            this.locationResults       = []; // close dropdown
        },

        async updateProfile() {
            console.log('Submitting:', JSON.stringify(this.editForm, null, 2));

            try {
                const response = await fetch('{{ route("provider.profile.update") }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.editForm)
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        let msg = 'Validation failed:\n';
                        Object.keys(data.errors).forEach(f => {
                            msg += `${f}: ${data.errors[f].join(', ')}\n`;
                        });
                        alert(msg);
                    } else {
                        alert('Update failed: ' + (data.message || 'Unknown error'));
                    }
                    return;
                }

                // Merge response back into profile so the page reflects changes immediately
                this.profile = { ...this.profile, ...data };
                this.openEditModal = false;

            } catch (err) {
                console.error('Update error:', err);
                alert('Network error. Please try again.');
            }
        }
    }
}
</script>
@endpush
