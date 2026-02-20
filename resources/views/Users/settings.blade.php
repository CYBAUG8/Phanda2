@extends('users.layout')
@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Panda</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Heroicons -->
    <script src="https://unpkg.com/@heroicons/react@24/outline/index.js"></script>
    
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        /* Custom toggle switch */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #f97316;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #f97316;
        }
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.2s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .modal-content {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="settingsData()" x-init="init()">
    <div class="p-4 sm:p-6 md:p-10 space-y-6 md:space-y-8 text-gray-900">
        <h1 class="text-2xl sm:text-3xl font-semibold mb-4 sm:mb-6 text-orange-500">Settings</h1>

        
       <!-- PERSONAL INFO -->
<section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
    <h2 class="text-lg font-semibold mb-4 text-orange-500">Personal Information</h2>

    <div class="space-y-3 text-gray-700">
        @php
            $userInfo = auth()->user();
            $fields = [
                'full_name' => $userInfo->full_name,
                'email'     => $userInfo->email,
                'phone'     => $userInfo->phone,
            ];
        @endphp

        @foreach ($fields as $key => $value)
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-3 gap-2 sm:gap-0 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                <div class="flex-1">
                    <span class="capitalize font-medium">{{ str_replace('_', ' ', $key) }}</span>
                    <span class="break-words capitalize">{{ $value ?? '' }}</span>
                </div>
                <button @click="openEditModal('{{ $key }}', '{{ $value }}')" 
                        class="text-orange-500 hover:text-orange-600 text-sm font-medium px-3 py-1 rounded-md hover:bg-orange-50 transition-colors duration-200 w-fit">
                    Edit
                </button>
            </div>
        @endforeach
    </div>
</section>


        <!-- SERVICE PREFERENCES -->
        <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-lg font-semibold mb-4 text-orange-500">Service Preferences</h2>

            <template x-if="loading.settings">
                <div class="text-center py-4">
                    <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-orange-500"></div>
                    <p class="mt-2 text-gray-500">Loading settings...</p>
                </div>
            </template>

            <div class="space-y-4" x-show="!loading.settings">
                <div class="flex items-center justify-between border-b pb-3 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                    <div>
                        <span class="font-medium">Allow same-gender service providers only</span>
                        <p class="text-sm text-gray-600 mt-1">Only match with providers of your gender</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="settings.same_gender_provider" @change="toggleSetting('same_gender_provider', $event.target.checked)" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:bg-white"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between border-b pb-3 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                    <div>
                        <span class="font-medium">Auto-approve repeat providers</span>
                        <p class="text-sm text-gray-600 mt-1">Automatically book providers you've used before</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="settings.repeat_providers" @change="toggleSetting('repeat_providers', $event.target.checked)" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:bg-white"></div>
                    </label>
                </div>
            </div>
        </section>

        <!-- SAFETY & SHARING -->
        <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-lg font-semibold mb-4 text-orange-500">Safety & Sharing</h2>

            <div class="space-y-4">
                <div class="border-b pb-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <span class="font-medium">Emergency Contact</span>
                            <p class="text-sm text-gray-600 mt-1" x-text="emergencyContact ? emergencyContact.name + ' - ' + emergencyContact.phone : 'Share your service details with a trusted contact'"></p>
                        </div>
                        <button @click="openEmergencyContactModal()" class="text-orange-500 hover:text-orange-600 text-sm font-medium px-3 py-1 rounded-md hover:bg-orange-50 transition-colors duration-200">
                            <span x-text="emergencyContact ? 'Edit' : 'Add'"></span>
                        </button>
                    </div>

                    <template x-if="emergencyContact">
                        <div class="space-y-3 bg-gray-50 p-3 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-medium">Auto-share service details</span>
                                    <p class="text-sm text-gray-600">Automatically share service requests with your emergency contact</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="settings.auto_share" @change="toggleSetting('auto_share', $event.target.checked)" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:bg-white"></div>
                                </label>
                            </div>

                            <div class="flex gap-2">
                                <button @click="testShare()" class="flex items-center gap-2 px-3 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition-colors duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                    </svg>
                                    Test Share
                                </button>
                                <button @click="removeEmergencyContact()" class="px-3 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300 transition-colors duration-200">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <template x-if="emergencyContact && settings.auto_share">
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <h4 class="font-medium text-orange-800 mb-2">What gets shared automatically:</h4>
                        <ul class="text-sm text-orange-700 space-y-1">
                            <li>• Service provider's name and photo</li>
                            <li>• Provider's vehicle/details (if applicable)</li>
                            <li>• Your pickup/dropoff locations</li>
                            <li>• Service type and estimated duration</li>
                            <li>• Real-time service status updates</li>
                        </ul>
                    </div>
                </template>
            </div>
        </section>

        <!-- SAVED LOCATIONS -->
        <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-lg font-semibold mb-4 text-orange-500">Saved Locations</h2>

            <template x-if="loading.locations">
                <div class="text-center py-4">
                    <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-orange-500"></div>
                    <p class="mt-2 text-gray-500">Loading locations...</p>
                </div>
            </template>

            <div class="text-gray-700 space-y-3" x-show="!loading.locations">
                <template x-for="(location, index) in savedLocations" :key="location.location_id || index">

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-3 gap-2 sm:gap-0 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200 group">
                        <div class="flex-1">
                            <div class="font-medium capitalize" x-text="location.name"></div>
                            <div class="text-sm text-gray-600 break-words" x-text="location.address"></div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="editLocation(location)" class="text-blue-500 hover:text-blue-600 p-1 rounded-md hover:bg-blue-50 transition-colors duration-200" title="Edit location">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button @click="deleteLocation(location.location_id)" class="text-red-500 hover:text-red-600 p-1 rounded-md hover:bg-red-50 transition-colors duration-200" title="Delete location">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>

                <div class="flex items-center justify-between pt-2 cursor-pointer hover:bg-gray-50 px-2 py-2 rounded transition-colors duration-200 group" @click="addLocation()">
                    <span class="text-orange-500 font-medium">Add Custom Place</span>
                    <div class="p-1 rounded-full bg-orange-500 text-white group-hover:bg-orange-600 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECURITY & PRIVACY -->
        <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-lg font-semibold mb-4 text-orange-500">Security & Privacy</h2>

            <div class="space-y-4">
                <div class="flex items-center justify-between border-b pb-3 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200 cursor-pointer" @click="openPasswordModal()">
                    <div>
                        <span class="font-medium">Update Password</span>
                        <p class="text-sm text-gray-600 mt-1">Change your account password</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>

                <div class="flex items-center justify-between border-b pb-3 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200 cursor-pointer" @click="openRecoveryContactModal()">
                    <div>
                        <span class="font-medium">Recovery Contact</span>
                        <p class="text-sm text-gray-600 mt-1" x-text="recoveryContact ? recoveryContact.name + ' - ' + recoveryContact.phone : 'Add someone to help recover your account'"></p>
                    </div>
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>

                <div class="flex items-center justify-between border-b pb-3 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200 cursor-pointer" @click="openLoginHistoryModal()">
                    <div>
                        <span class="font-medium">Login History</span>
                        <p class="text-sm text-gray-600 mt-1">View recent account activity</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>

              

                <div class="flex items-center justify-between border-b pb-3 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                    <div>
                        <span>Two-Factor Authentication (2FA)</span>
                        <p class="text-sm text-gray-600 mt-1">Add an extra layer of security to your account</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="settings.two_factor_auth" @change="toggleSetting('two_factor_auth', $event.target.checked)" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:bg-white"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between border-b pb-3 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                    <span>Data sharing</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="dataShare" @change="toggleDataShare($event.target.checked)" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:bg-white"></div>
                    </label>
                </div>
            </div>
        </section>

        <!-- NOTIFICATIONS -->
        <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-lg font-semibold mb-4 text-orange-500">Notifications</h2>

            <div class="space-y-4">
                <div class="flex items-center justify-between border-b pb-3 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                    <span>Notifications</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="settings.notifications" @change="toggleSetting('notifications', $event.target.checked)" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:bg-white"></div>
                    </label>
                </div>
            </div>
        </section>

        <!-- DATA MANAGEMENT -->
        <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-lg font-semibold mb-4 text-orange-500">Data Management</h2>

            <div class="flex flex-col sm:flex-row gap-3">
                <button @click="downloadData()" class="flex items-center justify-center gap-2 px-4 py-2 bg-white border-2 border-orange-500 text-orange-500 font-medium rounded-lg hover:bg-orange-50 transition-all duration-200 w-full sm:w-auto transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    <span>Download My Data</span>
                </button>

                <button @click="openDeleteModal()" class="flex items-center justify-center gap-2 px-4 py-2 bg-orange-500 text-white font-medium border-2 border-orange-500 rounded-lg hover:bg-orange-600 transition-all duration-200 w-full sm:w-auto transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span>Delete Account</span>
                </button>
            </div>
        </section>
    </div>

    <!-- DELETE MODAL -->
    <template x-if="deleteModal">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeModal('deleteModal')"></div>
            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-medium mb-3">Confirm Deletion</h3>
                    <p class="text-gray-600 mb-6">Are you sure you want to delete your account? This action cannot be undone.</p>
                    <div class="flex flex-col sm:flex-row justify-end gap-3">
                        <button @click="closeModal('deleteModal')" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button @click="deleteAccount()" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- EDIT PERSONAL INFO MODAL -->
    <template x-if="editModal">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeModal('editModal')"></div>

            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-medium mb-3 text-orange-500">
                        <span x-text="isEmailOrPhone(selectedField) ? 'Verify' : 'Edit'"></span>
                        <span x-text="' ' + formatFieldName(selectedField)"></span>
                    </h3>
                    <div x-html="renderEditField()"></div>
                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-2">
                        <button @click="closeModal('editModal')" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button @click="isEmailOrPhone(selectedField) ? verifyField() : saveEdit()" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                            <span x-text="isEmailOrPhone(selectedField) ? 'Verify' : 'Save'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- OTP VERIFICATION MODAL -->
    <template x-if="otpModal">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeModal('otpModal')"></div>
            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-medium mb-3 text-orange-500">Enter Verification Code</h3>
                    <p class="text-sm text-gray-600 mb-3" x-text="'We sent a 6-digit code to your ' + (pendingVerification?.field || '') + '.'"></p>

                    <input type="text" x-model="otp" @input="filterOtp($event)" maxlength="6" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent mb-2" placeholder="Enter 6-digit OTP">

                    <p x-show="otpError" class="text-sm text-red-500 mb-2" x-text="otpError"></p>

                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-2">
                        <button @click="closeModal('otpModal')" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button @click="verifyOtp()" :disabled="otp.length !== 6" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            Verify OTP
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- LOCATION MODAL -->
    <template x-if="locationModal">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeModal('locationModal')"></div>
            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-medium mb-3 text-orange-500" x-text="isEditingLocation ? 'Edit Location' : 'Add New Location'"></h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Location Name</label>
                            <input type="text" x-model="newLocation.name" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="e.g., Gym, Grandma's House">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea x-model="newLocation.address" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Enter full address" rows="3"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select x-model="newLocation.type" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="home">Home</option>
                                <option value="work">Work</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
                        <button @click="closeModal('locationModal')" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button @click="saveLocation()" :disabled="!newLocation.name.trim() || !newLocation.address.trim()" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <span x-text="isEditingLocation ? 'Update' : 'Save'"></span> Location
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- EMERGENCY CONTACT MODAL -->
    <template x-if="emergencyContactModal">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeModal('emergencyContactModal')"></div>
            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-medium mb-3 text-orange-500" x-text="emergencyContact ? 'Edit Emergency Contact' : 'Add Emergency Contact'"></h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Name</label>
                            <input type="text" x-model="emergencyContact.name" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="e.g., Sarah Mom">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" x-model="emergencyContact.phone" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="+27 82 123 4567">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
                            <select x-model="emergencyContact.relationship" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="">Select relationship</option>
                                <option value="Parent">Parent</option>
                                <option value="Spouse">Spouse</option>
                                <option value="Sibling">Sibling</option>
                                <option value="Friend">Friend</option>
                                <option value="Relative">Relative</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-medium text-blue-800 mb-2">How it works:</h4>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• Share service details automatically with this contact</li>
                                <li>• They receive provider info, location, and service details</li>
                                <li>• Updates are sent via SMS or app notification</li>
                                <li>• You can toggle auto-sharing on/off anytime</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
                        <button @click="closeModal('emergencyContactModal')" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button @click="saveEmergencyContact()" :disabled="!emergencyContact.name?.trim() || !emergencyContact.phone?.trim()" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <span x-text="emergencyContact ? 'Update' : 'Save'"></span> Contact
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- UPDATE PASSWORD MODAL -->
    <template x-if="passwordModal">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeModal('passwordModal')"></div>
            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-medium mb-3 text-orange-500">Update Password</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <div class="relative">
                                <input :type="showCurrentPassword ? 'text' : 'password'" x-model="passwordData.currentPassword" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent pr-10" placeholder="Enter current password">
                                <button type="button" @click="showCurrentPassword = !showCurrentPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <svg x-show="showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                    <svg x-show="!showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <div class="relative">
                                <input :type="showNewPassword ? 'text' : 'password'" x-model="passwordData.newPassword" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent pr-10" placeholder="Enter new password">
                                <button type="button" @click="showNewPassword = !showNewPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <svg x-show="showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                    <svg x-show="!showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <div class="relative">
                                <input :type="showConfirmPassword ? 'text' : 'password'" x-model="passwordData.confirmPassword" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent pr-10" placeholder="Confirm new password">
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <svg x-show="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                    <svg x-show="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-800 mb-2">Password Requirements:</h4>
                            <ul class="text-sm text-gray-700 space-y-1">
                                <li>• At least 6 characters long</li>
                                <li>• Include letters and numbers</li>
                                <li>• Should not match your current password</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
                        <button @click="closeModal('passwordModal')" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button @click="updatePassword()" :disabled="!passwordData.currentPassword || !passwordData.newPassword || !passwordData.confirmPassword" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            Update Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- RECOVERY CONTACT MODAL -->
    <template x-if="recoveryContactModal">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeModal('recoveryContactModal')"></div>
            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-medium mb-3 text-orange-500" x-text="recoveryContact ? 'Edit Recovery Contact' : 'Add Recovery Contact'"></h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Name</label>
                            <input type="text" x-model="recoveryContact.name" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="e.g., John Brother">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" x-model="recoveryContact.phone" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="+27 82 123 4567">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" x-model="recoveryContact.email" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="contact@example.com">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
                            <select x-model="recoveryContact.relationship" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="">Select relationship</option>
                                <option value="Parent">Parent</option>
                                <option value="Spouse">Spouse</option>
                                <option value="Sibling">Sibling</option>
                                <option value="Friend">Friend</option>
                                <option value="Relative">Relative</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-medium text-blue-800 mb-2">Recovery Contact Role:</h4>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• Help verify your identity if you're locked out</li>
                                <li>• Receive account recovery requests</li>
                                <li>• Assist in resetting your password if needed</li>
                                <li>• This person should be someone you trust completely</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
                        <button x-show="recoveryContact" @click="removeRecoveryContact()" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                            Remove
                        </button>
                        <button @click="closeModal('recoveryContactModal')" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button @click="saveRecoveryContact()" :disabled="!recoveryContact.name?.trim() || !recoveryContact.phone?.trim()" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <span x-text="recoveryContact ? 'Update' : 'Save'"></span> Contact
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- LOGIN HISTORY MODAL -->
    <template x-if="loginHistoryModal">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeModal('loginHistoryModal')"></div>
            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-4xl p-6">
                    <h3 class="text-lg font-medium mb-3 text-orange-500">Login History</h3>
                    <p class="text-gray-600 mb-4">Recent account activity and login attempts</p>

                    <template x-if="loading.loginHistory">
                        <div class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
                            <p class="mt-2 text-gray-500">Loading login history...</p>
                        </div>
                    </template>

                    <div class="overflow-x-auto" x-show="!loading.loginHistory">
                        <table class="w-full text-sm text-left text-gray-700">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">Date & Time</th>
                                    <th class="px-4 py-3">Device</th>
                                    <th class="px-4 py-3">Location</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(login, index) in loginHistory" :key="index">
                                    <tr class="border-b hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-4 py-3 font-medium" x-text="login.date"></td>
                                        <td class="px-4 py-3" x-text="login.device"></td>
                                        <td class="px-4 py-3" x-text="login.location"></td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium" :class="login.status === 'Success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" x-text="login.status"></span>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="loginHistory.length === 0">
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                            No login history found
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button @click="closeModal('loginHistoryModal')" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- PREFERRED CONTACT METHOD MODAL -->
    <template x-if="preferredMethodModal">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeModal('preferredMethodModal')"></div>
            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-sm p-6">
                    <h3 class="text-lg font-medium mb-3 text-orange-500">Preferred Contact Method</h3>
                    <div class="space-y-2">
                        <template x-for="opt in preferredOptions" :key="opt">
                            <label class="flex items-center gap-3 p-2 border rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="preferred-contact" x-model="preferredContactMethod" :value="opt">
                                <span x-text="opt"></span>
                            </label>
                        </template>
                    </div>
                    <div class="flex justify-end gap-3 mt-4">
                        <button @click="closeModal('preferredMethodModal')" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <script>
    function settingsData() {
        return {
            // Modal states
            deleteModal: false,
            editModal: false,
            locationModal: false,
            emergencyContactModal: false,
            passwordModal: false,
            recoveryContactModal: false,
            loginHistoryModal: false,
            preferredMethodModal: false,
            otpModal: false,

            // Settings data
            dataShare: localStorage.getItem('dataShare') === 'true',
            
            settings: {
                same_gender_provider: false,
                repeat_providers: false,
                auto_share: false,
                two_factor_auth: false,
                notifications: true,
            },

            loading: {
                userInfo: false,
                settings: false,
                locations: false,
                loginHistory: false,
            },

            otpError: '',
            otp: '',
            selectedField: '',
            editValue: '',
            editError: '',

            // Location
            isEditingLocation: false,
            newLocation: { name: '', address: '', type: 'home' },

            // Password
            passwordData: {
                currentPassword: '',
                newPassword: '',
                confirmPassword: '',
            },
            showCurrentPassword: false,
            showNewPassword: false,
            showConfirmPassword: false,

            // User data
            userInfo: {},
            savedLocations: [],
            emergencyContact: null,
            recoveryContact: JSON.parse(localStorage.getItem('recoveryContact')) || null,
            loginHistory: [],

            // Preferences
            preferredContactMethod: localStorage.getItem('preferredContactMethod') || 'Call',

            // Pending verification
            pendingVerification: null,

            // Constants
            preferredOptions: ['Call', 'SMS', 'WhatsApp', 'Email'],

            async init() {
                await this.fetchUserInfo();
                await this.fetchSettings();
                await this.fetchEmergencyContact();
                await this.fetchLocations();
                await this.fetchLoginHistory();
                await this.fetchRecoveryContact();
            },

            async fetchUserInfo() {
                try {
                    const response = await fetch('/userInfo', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });
                    const data = await response.json();
                    if (data.user) {
                        this.userInfo = data.user;
                    }
                } catch (error) {
                    console.error('Error fetching user info:', error);
                }
            },

            async fetchSettings() {
                this.loading.settings = true;
                try {
                    const response = await fetch('/getSettings', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });
                    const data = await response.json();
                    if (data.settings) {
                        this.settings = data.settings;
                    }
                } catch (error) {
                    console.error('Error fetching settings:', error);
                } finally {
                    this.loading.settings = false;
                }
            },

            async fetchRecoveryContact() {
    try {
        const response = await fetch('/recovery-contact', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });
        const data = await response.json();
        this.recoveryContact = data.recovery_contact || null;
    } catch (error) {
        console.error('Error fetching recovery contact:', error);
    }
},

            async fetchEmergencyContact() {
                try {
                    const response = await fetch('/emergency-contact', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });
                    const data = await response.json();
                    this.emergencyContact = data.emergency_contact || null;
                } catch (error) {
                    console.error('Error fetching emergency contact:', error);
                }
            },

            async fetchLocations() {
                this.loading.locations = true;
                try {
                    const response = await fetch('/locations', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.savedLocations = data.locations || [];
                    }
                } catch (error) {
                    console.error('Error fetching locations:', error);
                } finally {
                    this.loading.locations = false;
                }
            },

            async fetchLoginHistory() {
                this.loading.loginHistory = true;
                try {
                    const response = await fetch('/login-history', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.loginHistory = data.login_history || [];
                    }
                } catch (error) {
                    console.error('Error fetching login history:', error);
                } finally {
                    this.loading.loginHistory = false;
                }
            },

            validateEmail(email) {
                return /^\S+@\S+\.\S+$/.test(String(email || '').trim());
            },

            validateSAPhone(phone) {
                return /^(\+27|0)[6-8][0-9]{8}$/.test(String(phone || '').trim());
            },

            isEmailOrPhone(field) {
                return field === 'email' || field === 'phone';
            },

            formatFieldName(field) {
                return field.replace(/([A-Z])/g, " $1").replace(/^./, str => str.toUpperCase());
            },

            openEditModal(field, value) {
                this.selectedField = field;
                this.editValue = value || '';
                this.editError = '';
                this.editModal = true;
            },

            renderEditField() {
                const type = this.selectedField === 'email' ? 'email' : 'text';
                return `
                    <input type="${type}" x-model="editValue" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent mb-1 transition-all duration-200" placeholder="Enter your ${this.selectedField}">
                    <p x-show="editError" class="text-sm text-red-600" x-text="editError"></p>
                `;
            },

            async saveEdit() {
                try {
                    const response = await fetch('/updateUserInfo', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({
                            field: this.selectedField,
                            value: this.editValue
                        })
                    });

                    const data = await response.json();
                    
                    if (response.ok) {
                        this.editModal = false;
                        await this.fetchUserInfo();
                        alert('Information updated successfully!');
                    } else {
                        alert(data.message || 'Failed to update. Please try again.');
                    }
                } catch (error) {
                    console.error('Error updating user info:', error);
                    alert('Failed to update. Please try again.');
                }
            },

            async verifyField() {
                if (this.selectedField === 'email' && !this.validateEmail(this.editValue)) {
                    this.editError = 'Please enter a valid email address before verifying.';
                    return;
                }
                if (this.selectedField === 'phone' && !this.validateSAPhone(this.editValue)) {
                    this.editError = 'Enter a valid SA phone number before verifying.';
                    return;
                }

                try {
                    const response = await fetch('/sendOtp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        }
                    });

                    const data = await response.json();
                    
                    if (response.ok) {
                        this.pendingVerification = {
                            field: this.selectedField,
                            value: this.editValue,
                        };
                        this.editModal = false;
                        this.otpModal = true;
                        this.otp = '';
                        this.otpError = '';
                    } else {
                        alert(data.message || 'Failed to send verification code.');
                    }
                } catch (error) {
                    console.error('Error sending OTP:', error);
                    alert('An error occurred while sending verification code.');
                }
            },

            filterOtp(event) {
                this.otp = event.target.value.replace(/\D/g, '').slice(0, 6);
                this.otpError = '';
            },

            async verifyOtp() {
                if (!this.pendingVerification) {
                    this.otpError = 'No pending verification found. Please try again.';
                    return;
                }

                if (!this.otp || this.otp.length !== 6) {
                    this.otpError = 'Please enter a valid 6-digit OTP.';
                    return;
                }

                try {
                    const response = await fetch('/updateUserInfo', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({
                            field: this.pendingVerification.field,
                            value: this.pendingVerification.value,
                            otp: this.otp
                        })
                    });

                    const data = await response.json();
                    
                    if (response.ok) {
                        this.otpModal = false;
                        this.otp = '';
                        this.otpError = '';
                        this.pendingVerification = null;
                        await this.fetchUserInfo();
                        alert('Information verified successfully!');
                    } else {
                        this.otpError = data.message || 'Invalid OTP. Please try again.';
                    }
                } catch (error) {
                    console.error('Error verifying OTP:', error);
                    this.otpError = 'An error occurred. Please try again.';
                }
            },

            async toggleSetting(key, value) {
                const previousValue = this.settings[key];
                this.settings[key] = value;

                try {
                    const response = await fetch('/settings', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ key, value })
                    });

                    if (!response.ok) {
                        this.settings[key] = previousValue;
                        throw new Error('Failed to update settings');
                    }
                } catch (error) {
                    console.error('Error updating settings:', error);
                    alert('Failed to update settings. Please try again.');
                }
            },

            toggleDataShare(value) {
                this.dataShare = value;
                localStorage.setItem('dataShare', value);
            },

            addLocation() {
                this.newLocation = { name: '', address: '', type: 'home' };
                this.isEditingLocation = false;
                this.locationModal = true;
            },

            editLocation(location) {
                this.newLocation = { ...location, location_id: location.location_id }; 
                this.isEditingLocation = true;
                this.locationModal = true;
            },

            async saveLocation() {
                const clean = {
                    ...this.newLocation,
                    name: this.newLocation.name.trim(),
                    address: this.newLocation.address.trim()
                };
                
                if (!clean.name || !clean.address) {
                    alert('Please fill in both location name and address');
                    return;
                }

                try {
                    const url = this.isEditingLocation && this.newLocation.location_id 
                        ? `/locations/${this.newLocation.location_id}`
                        : '/locations';
                    
                    const method = this.isEditingLocation && this.newLocation.location_id ? 'PUT' : 'POST';
                    
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify(clean)
                    });

                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        this.locationModal = false;
                        await this.fetchLocations();
                        alert('Location saved successfully!');
                    } else {
                        alert(data.message || 'Failed to save location');
                    }
                } catch (error) {
                    console.error('Error saving location:', error);
                    alert('Failed to save location. Please try again.');
                }
            },

            async deleteLocation(id) {
                if (!confirm('Delete this location?')) return;
                
                try {
                    const response = await fetch(`/locations/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        }
                    });

                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        await this.fetchLocations();
                        alert('Location deleted successfully!');
                    } else {
                        alert(data.message || 'Failed to delete location');
                    }
                } catch (error) {
                    console.error('Error deleting location:', error);
                    alert('Failed to delete location. Please try again.');
                }
            },

            openEmergencyContactModal() {
                if (!this.emergencyContact) {
                    this.emergencyContact = { name: '', phone: '', relationship: '' };
                }
                this.emergencyContactModal = true;
            },

            async saveEmergencyContact() {
                if (!this.emergencyContact?.name?.trim() || !this.emergencyContact?.phone?.trim()) {
                    alert('Please fill in both name and phone number');
                    return;
                }

                try {
                    const method = this.emergencyContact.id ? 'PUT' : 'POST';
                    const response = await fetch('/emergency-contact', {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({
                            name: this.emergencyContact.name,
                            phone: this.emergencyContact.phone,
                            relationship: this.emergencyContact.relationship,
                        })
                    });

                    const data = await response.json();
                    
                    if (response.ok) {
                        this.emergencyContact = data.emergency_contact;
                        this.emergencyContactModal = false;
                        alert('Emergency contact saved successfully!');
                    } else {
                        alert(data.message || 'Failed to save emergency contact');
                    }
                } catch (error) {
                    console.error(error);
                    alert('Could not save emergency contact. Please try again.');
                }
            },

            async removeEmergencyContact() {
                if (!confirm('Remove emergency contact?')) return;

                try {
                    const response = await fetch('/emergency-contact', {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        }
                    });

                    if (response.ok) {
                        this.emergencyContact = null;
                        this.settings.auto_share = false;
                        alert('Emergency contact removed.');
                        await this.fetchSettings();
                    }
                } catch (error) {
                    console.error(error);
                    alert('Failed to remove emergency contact');
                }
            },

            testShare() {
                if (this.emergencyContact) {
                    alert(`Test message would be sent to ${this.emergencyContact.name} at ${this.emergencyContact.phone}.`);
                } else {
                    alert('Please add an emergency contact first');
                }
            },

            openPasswordModal() {
                this.passwordData = { currentPassword: '', newPassword: '', confirmPassword: '' };
                this.passwordModal = true;
            },

            async updatePassword() {
    const { currentPassword, newPassword, confirmPassword } = this.passwordData;
    
    if (!currentPassword || !newPassword || !confirmPassword) {
        alert('Please fill in all password fields');
        return;
    }

    if (newPassword !== confirmPassword) {
        alert('New password and confirm password do not match!');
        return;
    }

    if (newPassword.length < 6 || !/[A-Za-z]/.test(newPassword) || !/[0-9]/.test(newPassword)) {
        alert('Password must be at least 6 characters, and include letters and numbers.');
        return;
    }

    if (newPassword === currentPassword) {
        alert('New password must be different from current password.');
        return;
    }

    try {
        const response = await fetch('/update-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                new_password_confirmation: confirmPassword
            })
        });

        const data = await response.json();
        
        if (response.ok) {
            alert('Password updated successfully!');
            this.passwordModal = false;
            this.passwordData = { currentPassword: '', newPassword: '', confirmPassword: '' };
        } else {
            alert(data.message || 'Failed to update password');
        }
    } catch (error) {
        console.error('Error updating password:', error);
        alert('Failed to update password. Please try again.');
    }
},


            openRecoveryContactModal() {
                if (!this.recoveryContact) {
                    this.recoveryContact = { name: '', phone: '', email: '', relationship: '' };
                }
                this.recoveryContactModal = true;
            },

            async saveRecoveryContact() {
    if (!this.recoveryContact?.name?.trim() || !this.recoveryContact?.phone?.trim()) {
        alert('Please fill in both name and phone number');
        return;
    }

    try {
        const method = this.recoveryContact.recovery_contact_id ? 'PUT' : 'POST';
        const response = await fetch('/recovery-contact', {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                name: this.recoveryContact.name,
                phone: this.recoveryContact.phone,
                email: this.recoveryContact.email,
                relationship: this.recoveryContact.relationship,
            })
        });

        const data = await response.json();
        
        if (response.ok) {
            this.recoveryContact = data.recovery_contact;
            this.recoveryContactModal = false;
            alert('Recovery contact saved successfully!');
        } else {
            alert(data.message || 'Failed to save recovery contact');
        }
    } catch (error) {
        console.error(error);
        alert('Could not save recovery contact. Please try again.');
    }
},

            async removeRecoveryContact() {
    if (!confirm('Remove recovery contact?')) return;

    try {
        const response = await fetch('/recovery-contact', {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        });

        if (response.ok) {
            this.recoveryContact = null;
            alert('Recovery contact removed.');
        } else {
            alert('Failed to remove recovery contact');
        }
    } catch (error) {
        console.error(error);
        alert('Failed to remove recovery contact');
    }
},

            openLoginHistoryModal() {
                this.fetchLoginHistory();
                this.loginHistoryModal = true;
            },

            openPreferredMethodModal() {
                this.preferredMethodModal = true;
            },

            downloadData() {
                const payload = {
                    userInfo: this.userInfo,
                    settings: this.settings,
                    savedLocations: this.savedLocations,
                    emergencyContact: this.emergencyContact,
                    recoveryContact: this.recoveryContact,
                    loginHistory: this.loginHistory,
                    exportedAt: new Date().toISOString(),
                };
                
                const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'my-panda-settings.json';
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
            },

            openDeleteModal() {
                this.deleteModal = true;
            },

            async deleteAccount() {
                try {
                    const response = await fetch('/profile', {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        }
                    });

                    const data = await response.json();
                    
                    if (response.ok) {
                        localStorage.clear();
                        alert('Your account has been deleted');
                        window.location.href = '/';
                    } else {
                        alert(data.message || 'Failed to delete account');
                    }
                } catch (error) {
                    console.error(error);
                    alert('Failed to delete account');
                }
            },

            closeModal(modalName) {
                this[modalName] = false;
            }
        }
    }
    </script>
</body>
</html>
@endsection