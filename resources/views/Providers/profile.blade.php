@extends('providers.layout')

@section('content')
<div class="p-6 space-y-8" 
     x-data="providerProfile({{ json_encode($provider) }})" 
     x-init="init()">

    <!-- PROFILE HEADER -->
    <section class="bg-white border rounded-lg p-8 shadow-sm">
        <div class="flex items-center gap-6">
            <!-- Avatar -->
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
        <a href="provider/services" 
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

    <!-- BUSINESS INFORMATION with Edit Button -->
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
            <span class="capitalize font-medium" x-text="provider.kyc_status"></span>
        </div>
    </section>

    <!-- EDIT PROFILE MODAL (now inside the same component) -->
    <div x-show="openEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75"
         @click.self="openEditModal = false">
        
        <!-- Modal panel -->
        <div x-transition
             class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6"
             @click.stop>
             
            <!-- Modal header -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Business Information</h3>
                <button type="button" @click="openEditModal = false" class="text-gray-500 hover:text-gray-700">
                    ✕
                </button>
            </div>

            <!-- Modal form -->
            <form @submit.prevent="updateProfile" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Business Name</label>
                    <input type="text" x-model="editForm.business_name" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" x-model="editForm.phone" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Service Area</label>
                    <input type="text" x-model="editForm.service_area" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Years of Experience</label>
                    <input type="number" x-model="editForm.years_experience" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>

                <!-- Buttons -->
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
        editForm: {
            business_name: '',
            phone: '',
            service_area: '',
            years_experience: ''
        },
        init() {
            // When modal opens, populate the form with current profile data
            this.$watch('openEditModal', (value) => {
                if (value) {
                    this.editForm = {
                        business_name: this.profile.business_name,
                        phone: this.profile.phone,
                        service_area: this.profile.service_area,
                        years_experience: this.profile.years_experience
                    };
                }
            });
        },
        getInitials() {
            if (!this.profile.business_name) return '';
            return this.profile.business_name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0,2);
        },
        async updateProfile() {
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

                if (!response.ok) {
                    let errorMsg = 'Update failed';
                    try {
                        const errorData = await response.json();
                        errorMsg = errorData.message || errorMsg;
                    } catch (e) {
                        errorMsg = response.statusText;
                    }
                    alert(errorMsg);
                    return;
                }

                const updated = await response.json();
                this.profile = { ...this.profile, ...updated };
                this.openEditModal = false;
                
            } catch (error) {
                console.error('Update error:', error);
                alert('Network error. Please try again.');
            }
        }
    }
}
</script>
@endpush