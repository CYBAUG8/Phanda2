@extends('providers.layout')

@section('content')
<div class="p-6 space-y-8" x-data="providerProfile({{ json_encode($provider) }})" x-init="init()">
    <section class="bg-white border rounded-lg p-8 shadow-sm">
        <div class="flex items-center gap-6">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white text-3xl font-bold shadow">
                <span x-text="getInitials()"></span>
            </div>
            <div class="flex-1">
                <span class="px-3 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full">Service Provider</span>
                <h1 class="text-3xl font-bold mt-2" x-text="profile.business_name"></h1>
                <p class="text-gray-600" x-text="profile.email"></p>
                <div class="flex items-center gap-2 mt-2"><span class="text-yellow-500">★</span><span class="font-medium" x-text="profile.rating_avg || '0.0'"></span></div>
            </div>
        </div>
    </section>

    <section class="bg-white border rounded-lg p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-orange-500 mb-4">Account Summary</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-4 border rounded-lg"><div class="text-2xl font-bold text-orange-500" x-text="profile.total_bookings || 0"></div><div class="text-gray-600 text-sm">Total Bookings</div></div>
            <div class="text-center p-4 border rounded-lg"><div class="text-2xl font-bold text-orange-500" x-text="profile.active_jobs || 0"></div><div class="text-gray-600 text-sm">Active Jobs</div></div>
            <div class="text-center p-4 border rounded-lg"><div class="text-2xl font-bold text-orange-500" x-text="profile.completed_jobs || 0"></div><div class="text-gray-600 text-sm">Completed</div></div>
            <div class="text-center p-4 border rounded-lg"><div class="text-2xl font-bold text-green-600" x-text="'R ' + Number(profile.total_earnings || 0).toFixed(2)"></div><div class="text-gray-600 text-sm">Total Earnings</div></div>
        </div>
    </section>

    <section class="bg-white border rounded-lg p-6 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-orange-500">Business Information</h2>
            <button @click="openEditModal = true" class="text-orange-500 hover:text-orange-600 text-sm font-medium">Edit</button>
        </div>
        <div class="space-y-3 text-gray-700">
            <div class="flex justify-between border-b pb-3"><span class="font-medium">Business Name:</span><span x-text="profile.business_name"></span></div>
            <div class="flex justify-between border-b pb-3"><span class="font-medium">Phone:</span><span x-text="profile.phone"></span></div>
            <div class="flex justify-between border-b pb-3"><span class="font-medium">Service Area:</span><span x-text="profile.service_area || 'Not set'"></span></div>
            <div class="flex justify-between border-b pb-3"><span class="font-medium">Service Radius:</span><span x-text="(profile.service_radius_km || 25) + ' km'"></span></div>
            <div class="flex justify-between border-b pb-3"><span class="font-medium">Years of Experience:</span><span x-text="(profile.years_experience || 0) + ' years'"></span></div>
        </div>
    </section>

    <section class="bg-white border rounded-lg p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-orange-500 mb-4">Account Status</h2>
        <div class="flex items-center gap-3">
            <div class="w-3 h-3 rounded-full" :class="profile.kyc_status === 'APPROVED' ? 'bg-green-500' : 'bg-yellow-500'"></div>
            <span class="capitalize font-medium" x-text="profile.kyc_status"></span>
        </div>
    </section>

    <div x-show="openEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75" @click.self="openEditModal = false">
        <div x-transition class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6" @click.stop>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Business Information</h3>
                <button type="button" @click="openEditModal = false" class="text-gray-500 hover:text-gray-700">X</button>
            </div>
            <form @submit.prevent="updateProfile" class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Business Name</label><input type="text" x-model="editForm.business_name" class="mt-1 block w-full border-gray-300 rounded-md"></div>
                <div><label class="block text-sm font-medium text-gray-700">Phone</label><input type="text" x-model="editForm.phone" class="mt-1 block w-full border-gray-300 rounded-md"></div>
                <div><label class="block text-sm font-medium text-gray-700">Service Area</label><input type="text" x-model="editForm.service_area" class="mt-1 block w-full border-gray-300 rounded-md"></div>
                <div><label class="block text-sm font-medium text-gray-700">Service Radius (km)</label><input type="number" min="1" max="100" x-model="editForm.service_radius_km" class="mt-1 block w-full border-gray-300 rounded-md"></div>
                <div><label class="block text-sm font-medium text-gray-700">Years of Experience</label><input type="number" x-model="editForm.years_experience" class="mt-1 block w-full border-gray-300 rounded-md"></div>
                <div>
                    <button type="button" class="px-3 py-2 bg-gray-200 rounded" @click="useCurrentLocation">Use Current Location</button>
                    <p class="text-xs text-gray-500 mt-1" x-text="locationPreview"></p>
                </div>
                <div class="flex justify-end gap-2 mt-4"><button type="button" @click="openEditModal = false" class="px-4 py-2 bg-gray-200 rounded-md">Cancel</button><button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-md">Save</button></div>
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
        locationPreview: '',
        editForm: { business_name: '', phone: '', service_area: '', years_experience: '', service_radius_km: 25, last_lat: null, last_lng: null },
        init() {
            this.$watch('openEditModal', (value) => {
                if (value) {
                    this.editForm = {
                        business_name: this.profile.business_name,
                        phone: this.profile.phone,
                        service_area: this.profile.service_area,
                        years_experience: this.profile.years_experience,
                        service_radius_km: this.profile.service_radius_km || 25,
                        last_lat: this.profile.last_lat,
                        last_lng: this.profile.last_lng,
                    };
                    this.locationPreview = this.editForm.last_lat && this.editForm.last_lng
                        ? `Current: ${this.editForm.last_lat}, ${this.editForm.last_lng}`
                        : 'No provider location set.';
                }
            });
        },
        getInitials() {
            if (!this.profile.business_name) return '';
            return this.profile.business_name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0,2);
        },
        async reverseGeocode(lat, lng) {
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`);
                if (!res.ok) return null;
                return await res.json();
            } catch (_) {
                return null;
            }
        },
        useCurrentLocation() {
            if (!navigator.geolocation) {
                alert('Geolocation not supported.');
                return;
            }
            navigator.geolocation.getCurrentPosition(async (pos) => {
                this.editForm.last_lat = Number(pos.coords.latitude.toFixed(7));
                this.editForm.last_lng = Number(pos.coords.longitude.toFixed(7));
                const geo = await this.reverseGeocode(this.editForm.last_lat, this.editForm.last_lng);
                if (geo && geo.address) {
                    const a = geo.address;
                    const locality = a.suburb || a.neighbourhood || a.city || a.town || a.village || a.county || '';
                    const city = a.city || a.town || a.village || a.county || '';
                    this.editForm.service_area = [locality, city].filter(Boolean).filter((v, i, arr) => arr.indexOf(v) === i).join(', ') || this.editForm.service_area;
                    this.locationPreview = geo.display_name || `Selected: ${this.editForm.last_lat}, ${this.editForm.last_lng}`;
                } else {
                    this.locationPreview = `Selected: ${this.editForm.last_lat}, ${this.editForm.last_lng}`;
                }
            }, () => alert('Could not fetch location.'), { enableHighAccuracy: true, timeout: 10000 });
        },
        async updateProfile() {
            try {
                const response = await fetch('{{ route("provider.profile.update") }}', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify(this.editForm)
                });
                const data = await response.json();
                if (!response.ok) {
                    alert(data.message || 'Update failed');
                    return;
                }
                this.profile = { ...this.profile, ...data };
                this.openEditModal = false;
            } catch (error) {
                console.error(error);
                alert('Network error. Please try again.');
            }
        }
    }
}
</script>
@endpush

