@extends('users.layout')
@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Panda</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Heroicons -->
    <script src="https://unpkg.com/@heroicons/react@24/outline/index.js"></script>
    
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        /* Custom animations */
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
        @keyframes slide-in {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
        /* Smooth transitions */
        * {
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        /* Fix for input cursor jumping */
        input, select, textarea {
            transition: none !important;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-900" x-data="profileData()" x-init="init()">
    <!-- Main Container -->
    <div class="p-4 sm:p-6 md:p-10 space-y-6 md:space-y-8">
        <!-- PROFILE HEADER -->
        <section class="bg-white border border-gray-200 rounded-lg p-6 sm:p-8 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                <!-- Initials Circle -->
                <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-white shadow-lg bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center">
                    <span class="text-white text-3xl font-bold" x-text="getInitials()"></span>
                </div>
                
                <!-- User Info -->
                <div class="flex-1 text-center sm:text-left">
                    <div class="mb-2">
                        <span class="inline-block px-3 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full mb-2">
                            <span x-text="profile.role || 'Customer'"></span>
                        </span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-1" x-text="getFullName()"></h1>
                    <p class="text-gray-600" x-text="profile.email"></p>
                </div>
            </div>
        </section>

        <!-- ACCOUNT SUMMARY -->
        <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-lg font-semibold mb-4 text-orange-500">Account Summary</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Total Service Requests -->
                <div class="text-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    <div class="text-2xl font-bold text-orange-500" x-text="profile.total_requests || 0"></div>
                    <div class="text-gray-600 text-sm mt-1">Total Requests</div>
                </div>
                
                <!-- Active Requests -->
                <div class="text-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    <div class="text-2xl font-bold text-orange-500" x-text="profile.active_requests || 0"></div>
                    <div class="text-gray-600 text-sm mt-1">Active</div>
                </div>
                
                <!-- Completed Requests -->
                <div class="text-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    <div class="text-2xl font-bold text-orange-500" x-text="profile.completed_requests || 0"></div>
                    <div class="text-gray-600 text-sm mt-1">Completed</div>
                </div>
                
                <!-- Member Since -->
                <div class="text-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    <div class="text-2xl font-bold text-orange-500" x-text="formatShortDate(profile.created_at)"></div>
                    <div class="text-gray-600 text-sm mt-1">Member Since</div>
                </div>
            </div>
        </section>

        <!-- PERSONAL INFORMATION -->
        <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-lg font-semibold mb-4 text-orange-500">Personal Information</h2>
            
            <div class="space-y-3 text-gray-700">
                <!-- Full Name -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-3 gap-2 sm:gap-0 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                    <div class="flex-1">
                        <span class="font-medium">Full Name:</span>
                        <span class="ml-2" x-text="getFullName() || 'Not set'"></span>
                    </div>
                    <button @click="openEditModal('full_name', getFullName())" class="text-orange-500 hover:text-orange-600 text-sm font-medium px-3 py-1 rounded-md hover:bg-orange-50 transition-colors duration-200 w-fit">
                        Edit
                    </button>
                </div>

                <!-- Email -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-3 gap-2 sm:gap-0 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Email:</span>
                            <span x-text="profile.email || 'Not set'"></span>
                            <div x-show="profile.email_verified" class="flex items-center gap-1 text-green-600 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Verified
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="openEditModal('email', profile.email)" class="text-orange-500 hover:text-orange-600 text-sm font-medium px-3 py-1 rounded-md hover:bg-orange-50 transition-colors duration-200">
                            Edit
                        </button>
                        <button x-show="profile.email && !profile.email_verified" @click="sendVerification('email')" class="text-blue-500 hover:text-blue-600 text-sm font-medium px-3 py-1 rounded-md hover:bg-blue-50 transition-colors duration-200">
                            Verify
                        </button>
                    </div>
                </div>

                <!-- Phone -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-3 gap-2 sm:gap-0 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Phone:</span>
                            <span x-text="profile.phone || 'Not set'"></span>
                            <div x-show="profile.phone_verified" class="flex items-center gap-1 text-green-600 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Verified
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="openEditModal('phone', profile.phone)" class="text-orange-500 hover:text-orange-600 text-sm font-medium px-3 py-1 rounded-md hover:bg-orange-50 transition-colors duration-200">
                            Edit
                        </button>
                        <button x-show="profile.phone && !profile.phone_verified" @click="sendVerification('phone')" class="text-blue-500 hover:text-blue-600 text-sm font-medium px-3 py-1 rounded-md hover:bg-blue-50 transition-colors duration-200">
                            Verify
                        </button>
                    </div>
                </div>

                <!-- Gender -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-3 gap-2 sm:gap-0 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                    <div class="flex-1">
                        <span class="font-medium">Gender:</span>
                        <span class="ml-2" x-text="profile.gender || 'Not set'"></span>
                    </div>
                    <button @click="openEditModal('gender', profile.gender)" class="text-orange-500 hover:text-orange-600 text-sm font-medium px-3 py-1 rounded-md hover:bg-orange-50 transition-colors duration-200 w-fit">
                        Edit
                    </button>
                </div>

            </div>
        </section>

        <!-- ADDRESS INFORMATION -->
        <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-orange-500">Address Information</h2>
                <button @click="openAddressModal()" class="flex items-center gap-2 px-3 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Address
                </button>
            </div>
            
            <div class="space-y-4">
                <template x-if="profile.addresses && profile.addresses.length > 0">
                    <template x-for="(address, index) in profile.addresses" :key="index">
                        <div class="border-b pb-4 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200 group">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <h3 class="font-medium" x-text="address.type.charAt(0).toUpperCase() + address.type.slice(1) + ' Address'"></h3>
                                        <span x-show="address.is_default" class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full">Default</span>
                                    </div>
                                    
                                    <div class="space-y-1 text-gray-600">
                                        <p x-text="address.street"></p>
                                        <p x-text="address.city + ', ' + address.province"></p>
                                        <p x-text="address.postal_code"></p>
                                        <p x-text="address.country"></p>
                                    </div>
                                </div>
                                
                                <div class="flex gap-2">
                                    <button @click="editAddress(address)" class="text-blue-500 hover:text-blue-600 p-1 rounded-md hover:bg-blue-50 transition-colors duration-200" title="Edit address">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button @click="openDeleteAddressModal(address)" class="text-red-500 hover:text-red-600 p-1 rounded-md hover:bg-red-50 transition-colors duration-200" title="Delete address">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 mt-3">
                                <button @click="setDefaultAddress(address.address_id)" x-show="!address.is_default" class="text-sm text-blue-500 hover:text-blue-600 font-medium px-3 py-1 rounded-md hover:bg-blue-50 transition-colors duration-200">
                                    Set as Default
                                </button>
                            </div>
                        </div>
                    </template>
                </template>
                
                <template x-if="!profile.addresses || profile.addresses.length === 0">
                    <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <p class="text-gray-500">No addresses added yet</p>
                        <button @click="openAddressModal()" class="mt-2 text-orange-500 hover:text-orange-600 font-medium">
                            Add your first address
                        </button>
                    </div>
                </template>
            </div>
        </section>

        <!-- ADDITIONAL INFORMATION -->
        <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-lg font-semibold mb-4 text-orange-500">Additional Information</h2>
            
            <div class="space-y-3 text-gray-700">
                <!-- Account Created -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-3 gap-2 sm:gap-0 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                    <div class="flex-1">
                        <span class="font-medium">Account Created:</span>
                        <span class="ml-2" x-text="formatDate(profile.created_at)"></span>
                    </div>
                </div>
                
                <!-- Last Updated -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-3 gap-2 sm:gap-0 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                    <div class="flex-1">
                        <span class="font-medium">Last Updated:</span>
                        <span class="ml-2" x-text="formatDate(profile.updated_at)"></span>
                    </div>
                </div>
                
                <!-- Member ID -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-3 gap-2 sm:gap-0 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                    <div class="flex-1">
                        <span class="font-medium">Member ID:</span>
                        <span class="ml-2 font-mono text-sm" x-text="profile.member_id || 'N/A'"></span>
                    </div>
                </div>
                
                <!-- Account Status -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-3 gap-2 sm:gap-0 hover:bg-gray-50 px-2 py-1 rounded transition-colors duration-200">
                    <div class="flex-1">
                        <span class="font-medium">Account Status:</span>
                        <span class="ml-2 capitalize" x-text="profile.account_status"></span>
                        <div class="w-2 h-2 rounded-full inline-block ml-2" :class="profile.account_status === 'active' ? 'bg-green-500' : 'bg-red-500'"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PROFILE ACTIONS -->
        <section class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-lg font-semibold mb-4 text-orange-500">Profile Actions</h2>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <button @click="openPrivacySettings()" class="flex items-center justify-center gap-2 px-4 py-2 bg-white border-2 border-blue-500 text-blue-500 font-medium rounded-lg hover:bg-blue-50 transition-all duration-200 w-full sm:w-auto transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span>Privacy Settings</span>
                </button>

                <button @click="openNotificationSettings()" class="flex items-center justify-center gap-2 px-4 py-2 bg-white border-2 border-green-500 text-green-500 font-medium rounded-lg hover:bg-green-50 transition-all duration-200 w-full sm:w-auto transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span>Notifications</span>
                </button>

                <button @click="openDeleteAccountModal()" class="flex items-center justify-center gap-2 px-4 py-2 bg-orange-500 text-white font-medium border-2 border-orange-500 rounded-lg hover:bg-orange-600 transition-all duration-200 w-full sm:w-auto transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span>Delete Account</span>
                </button>
            </div>
        </section>
    </div>

    <!-- MODALS -->

    <!-- EDIT FIELD MODAL -->
    <template x-if="editModal.show">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeEditModal()"></div>
            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-medium mb-3 text-orange-500">
                        Edit <span x-text="formatFieldName(editModal.field)"></span>
                    </h3>
                    
                    <div>
                        <template x-if="editModal.field === 'gender'">
                            <select x-model="editModal.value" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                                <option value="prefer_not_to_say">Prefer not to say</option>
                            </select>
                        </template>
                        <template x-if="editModal.field !== 'gender'">
                            <input :type="editModal.field === 'email' ? 'email' : 'text'" 
                                   x-model="editModal.value" 
                                   class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        </template>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
                        <button @click="closeEditModal()" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button @click="saveEdit()" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- ADDRESS MODAL -->
    <template x-if="addressModal.show">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeAddressModal()"></div>
            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-lg font-medium mb-4 text-orange-500">
                        <span x-text="addressModal.isEditing ? 'Edit Address' : 'Add New Address'"></span>
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address Type</label>
                            <select x-model="addressModal.data.type" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="home">Home</option>
                                <option value="work">Work</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                            <input type="text" x-model="addressModal.data.street" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="123 Main Street">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                <input type="text" x-model="addressModal.data.city" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Johannesburg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                                <select x-model="addressModal.data.province" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <option value="">Select Province</option>
                                    <option value="gauteng">Gauteng</option>
                                    <option value="western_cape">Western Cape</option>
                                    <option value="kwa_zulu_natal">KwaZulu-Natal</option>
                                    <option value="eastern_cape">Eastern Cape</option>
                                    <option value="limpopo">Limpopo</option>
                                    <option value="mpumalanga">Mpumalanga</option>
                                    <option value="north_west">North West</option>
                                    <option value="free_state">Free State</option>
                                    <option value="northern_cape">Northern Cape</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                                <input type="text" x-model="addressModal.data.postal_code" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="2001">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                <select x-model="addressModal.data.country" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <option value="south_africa">South Africa</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <input type="checkbox" x-model="addressModal.data.is_default" id="default-address" class="w-4 h-4 text-orange-500 border-gray-300 rounded focus:ring-orange-500">
                            <label for="default-address" class="text-sm text-gray-700">Set as default address</label>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
                        <button @click="closeAddressModal()" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button @click="saveAddress()" :disabled="!addressModal.data.street || !addressModal.data.city || !addressModal.data.postal_code" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <span x-text="addressModal.isEditing ? 'Update Address' : 'Save Address'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- OTP VERIFICATION MODAL -->
    <template x-if="otpModal.show">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeOtpModal()"></div>
            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-medium mb-3 text-orange-500">Verify Your <span x-text="otpModal.field"></span></h3>
                    
                    <p class="text-sm text-gray-600 mb-4">
                        We've sent a 6-digit verification code to your <span x-text="otpModal.field"></span>.
                        Please enter it below.
                    </p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                            <input type="text" x-model="otpModal.code" @input="filterOtp($event)" maxlength="6" class="w-full border border-gray-300 rounded-lg p-3 text-center text-lg tracking-widest focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="000000">
                        </div>
                        
                        <div x-show="otpModal.error" class="text-sm text-red-600" x-text="otpModal.error"></div>
                        
                        <div class="text-center">
                            <button @click="resendOtp()" class="text-sm text-orange-500 hover:text-orange-600 font-medium">
                                Resend Code
                            </button>
                            <p class="text-xs text-gray-500 mt-1" x-text="'Code expires in ' + otpModal.timeLeft + ' seconds'"></p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
                        <button @click="closeOtpModal()" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button @click="verifyOtp()" :disabled="otpModal.code.length !== 6" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            Verify & Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- DELETE ADDRESS MODAL -->
    <template x-if="deleteAddressModal.show">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeDeleteAddressModal()"></div>
            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <div class="text-center mb-4">
                        <div class="mx-auto w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Delete Address</h3>
                        <p class="text-sm text-gray-500 mt-1">Are you sure you want to delete this address?</p>
                    </div>
                    
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-700">
                            <strong>Address:</strong> <span x-text="deleteAddressModal.addressStreet"></span>
                        </p>
                        <p class="text-sm text-red-700 mt-2">
                            This action cannot be undone.
                        </p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
                        <button @click="closeDeleteAddressModal()" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button @click="confirmDeleteAddress()" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                            Delete Address
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- DELETE ACCOUNT MODAL -->
    <template x-if="deleteModal.show">
        <div class="fixed inset-0 z-50">
            <div class="modal-overlay fixed inset-0" @click="closeDeleteModal()"></div>
            <div class="fixed z-50 inset-0 flex items-center justify-center p-4">
                <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <div class="text-center mb-4">
                        <div class="mx-auto w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Delete Your Account</h3>
                        <p class="text-sm text-gray-500 mt-1">This action cannot be undone</p>
                    </div>
                    
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-red-700">
                            <strong>Warning:</strong> Deleting your account will:
                        </p>
                        <ul class="text-sm text-red-700 mt-2 space-y-1">
                            <li>• Remove all your personal information</li>
                            <li>• Delete your booking history</li>
                            <li>• Cancel any upcoming services</li>
                            <li>• Remove your saved locations and preferences</li>
                        </ul>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Enter your password to confirm</label>
                            <input type="password" x-model="deleteModal.password" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Current password">
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <input type="checkbox" x-model="deleteModal.confirmed" id="confirm-delete" class="w-4 h-4 text-red-500 border-gray-300 rounded focus:ring-red-500">
                            <label for="confirm-delete" class="text-sm text-gray-700">I understand this action is irreversible</label>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
                        <button @click="closeDeleteModal()" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button @click="deleteAccount()" :disabled="!deleteModal.password || !deleteModal.confirmed" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            Delete Account Permanently
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- SUCCESS TOAST -->
    <template x-if="toast.show">
        <div class="fixed top-4 right-4 z-50 animate-slide-in">
            <div class="bg-green-50 border border-green-200 rounded-lg shadow-lg p-4 max-w-sm" :class="toast.type === 'error' ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200'">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5" :class="toast.type === 'error' ? 'text-red-500' : 'text-green-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium" :class="toast.type === 'error' ? 'text-red-800' : 'text-green-800'" x-text="toast.message"></p>
                    </div>
                    <button @click="toast.show = false" class="ml-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 hover:bg-gray-200 transition-colors duration-200">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </template>

<script>
function profileData() {
    return {
        profile: @json($user ?? []),
        loading: false,

        editModal: { 
            show: false, 
            field: '', 
            value: '', 
            error: '' 
        },

        addressModal: {
            show: false,
            isEditing: false,
            editingId: null,
            data: {
                type: 'home',
                street: '',
                city: '',
                province: '',
                postal_code: '',
                country: 'south_africa',
                is_default: false,
            }
        },

        otpModal: {
            show: false,
            field: '',
            value: '',
            code: '',
            error: '',
            timeLeft: 300,
            timer: null,
        },

        deleteModal: { 
            show: false, 
            password: '', 
            confirmed: false 
        },

        deleteAddressModal: {
            show: false,
            addressId: null,
            addressStreet: ''
        },

        toast: { 
            show: false, 
            message: '', 
            type: 'success', 
            timeout: null 
        },

        init() {
            this.loadProfile();
            this.setupEscapeListener();
        },

        setupEscapeListener() {
            document.addEventListener('keydown', e => {
                if (e.key !== 'Escape') return;
                if (this.editModal.show) this.closeEditModal();
                else if (this.addressModal.show) this.closeAddressModal();
                else if (this.otpModal.show) this.closeOtpModal();
                else if (this.deleteModal.show) this.closeDeleteModal();
                else if (this.deleteAddressModal.show) this.closeDeleteAddressModal();
            });
        },

        async loadProfile() {
            this.loading = true;
            try {
                const res = await fetch("{{ route('api.profile.get') }}", {
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                if (data.success) this.profile = data.profile;
            } catch (e) {
                console.error(e);
                this.showToast('Failed to load profile', 'error');
            } finally {
                this.loading = false;
            }
        },

        // Helper function to get full name
        getFullName() {
            // If the backend provides a full_name field, use it
            if (this.profile.full_name) {
                return this.profile.full_name;
            }
            // Otherwise combine first_name and last_name
            const firstName = this.profile.first_name || '';
            const lastName = this.profile.last_name || '';
            return `${firstName} ${lastName}`.trim() || 'Not set';
        },

        // Edit Modal Functions
        openEditModal(field, value) {
            this.editModal = {
                show: true,
                field: field,
                value: value || '',
                error: ''
            };
        },

        closeEditModal() {
            this.editModal = { 
                show: false, 
                field: '', 
                value: '', 
                error: '' 
            };
        },

        async saveEdit() {
            const { field, value } = this.editModal;

            if (!value?.trim()) {
                this.editModal.error = 'This field is required';
                return;
            }

            if (['email', 'phone'].includes(field)) {
                this.startOtpVerification(field, value);
                this.closeEditModal();
                return;
            }

            try {
                const res = await fetch("{{ route('api.profile.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ field, value })
                });

                const data = await res.json();
                if (data.success) {
                    // Update the profile based on the field
                    if (field === 'full_name') {
                        // If backend supports full_name, update it directly
                        // Otherwise you might need to split it into first_name and last_name
                        this.profile.full_name = value;
                    } else {
                        this.profile[field] = value;
                    }
                    this.showToast('Updated successfully');
                    this.closeEditModal();
                } else {
                    this.editModal.error = data.message ?? 'Update failed';
                }
            } catch (e) {
                console.error(e);
                this.editModal.error = 'Update failed';
            }
        },

        // Address Modal Functions
        openAddressModal() {
            this.addressModal = {
                show: true,
                isEditing: false,
                editingId: null,
                data: {
                    type: 'home',
                    street: '',
                    city: '',
                    province: '',
                    postal_code: '',
                    country: 'south_africa',
                    is_default: false,
                }
            };
        },

        closeAddressModal() {
            this.addressModal.show = false;
        },

        editAddress(address) {
            this.addressModal = {
                show: true,
                isEditing: true,
                editingId: address.address_id,
                data: {
                    type: address.type || 'home',
                    street: address.street || '',
                    city: address.city || '',
                    province: address.province || '',
                    postal_code: address.postal_code || '',
                    country: address.country || 'south_africa',
                    is_default: address.is_default || false,
                }
            };
        },

        async saveAddress() {
            try {
                let url, method;
                
                if (this.addressModal.isEditing) {
                    url = "{{ route('api.profile.address.update', ['id' => ':id']) }}".replace(':id', this.addressModal.editingId);
                    method = 'PUT';
                } else {
                    url = "{{ route('api.profile.address.store') }}";
                    method = 'POST';
                }
                
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.addressModal.data)
                });

                const data = await res.json();
                if (data.success) {
                    this.showToast('Address saved successfully');
                    this.closeAddressModal();
                    await this.loadProfile();
                } else {
                    this.showToast(data.message || 'Failed to save address', 'error');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Failed to save address', 'error');
            }
        },

        // Delete Address Modal Functions
        openDeleteAddressModal(address) {
            this.deleteAddressModal = {
                show: true,
                addressId: address.address_id,
                addressStreet: address.street
            };
        },

        closeDeleteAddressModal() {
            this.deleteAddressModal = {
                show: false,
                addressId: null,
                addressStreet: ''
            };
        },

        async confirmDeleteAddress() {
            try {
                const url = "{{ route('api.profile.address.destroy', ['id' => ':id']) }}".replace(':id', this.deleteAddressModal.addressId);
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await res.json();
                if (data.success) {
                    this.showToast('Address deleted successfully');
                    this.closeDeleteAddressModal();
                    await this.loadProfile();
                } else {
                    this.showToast(data.message || 'Failed to delete address', 'error');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Failed to delete address', 'error');
            }
        },

        async setDefaultAddress(addressId) {
            try {
                const url = "{{ route('api.profile.address.set-default', ['id' => ':id']) }}".replace(':id', addressId);
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await res.json();
                if (data.success) {
                    this.showToast('Default address updated');
                    await this.loadProfile();
                } else {
                    this.showToast(data.message || 'Failed to update default address', 'error');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Failed to update default address', 'error');
            }
        },

        // OTP Modal Functions
        startOtpVerification(field, value) {
            this.otpModal = {
                show: true,
                field: field,
                value: value,
                code: '',
                error: '',
                timeLeft: 300,
                timer: null
            };
            this.startOtpTimer();
            this.sendOtpRequest();
        },

        closeOtpModal() {
            clearInterval(this.otpModal.timer);
            this.otpModal = {
                show: false, 
                field: '', 
                value: '',
                code: '', 
                error: '', 
                timeLeft: 300, 
                timer: null
            };
        },

        async sendOtpRequest() {
            try {
                await fetch("{{ route('api.profile.send-otp') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        field: this.otpModal.field,
                        value: this.otpModal.value
                    })
                });
            } catch (e) {
                console.error(e);
                this.otpModal.error = 'Failed to send OTP';
            }
        },

        async verifyOtp() {
            if (this.otpModal.code.length !== 6) {
                this.otpModal.error = 'Invalid OTP';
                return;
            }

            try {
                const res = await fetch("{{ route('api.profile.verify-otp') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        field: this.otpModal.field,
                        value: this.otpModal.value,
                        otp: this.otpModal.code
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.profile[this.otpModal.field] = this.otpModal.value;
                    this.closeOtpModal();
                    this.showToast('Verified successfully');
                } else {
                    this.otpModal.error = data.message ?? 'Verification failed';
                }
            } catch (e) {
                console.error(e);
                this.otpModal.error = 'Verification failed';
            }
        },

        startOtpTimer() {
            clearInterval(this.otpModal.timer);
            this.otpModal.timer = setInterval(() => {
                if (--this.otpModal.timeLeft <= 0) {
                    clearInterval(this.otpModal.timer);
                    this.otpModal.error = 'OTP expired';
                }
            }, 1000);
        },

        resendOtp() {
            this.otpModal.timeLeft = 300;
            this.otpModal.code = '';
            this.otpModal.error = '';
            this.startOtpTimer();
            this.sendOtpRequest();
            this.showToast('New OTP sent');
        },

        filterOtp(event) {
            let value = event.target.value;
            value = value.replace(/\D/g, '').slice(0, 6);
            this.otpModal.code = value;
        },

        // Delete Account Modal Functions
        openDeleteAccountModal() {
            this.deleteModal = { 
                show: true, 
                password: '', 
                confirmed: false 
            };
        },

        closeDeleteModal() {
            this.deleteModal = { 
                show: false, 
                password: '', 
                confirmed: false 
            };
        },

        async deleteAccount() {
            try {
                const res = await fetch("{{ route('api.profile.delete') }}", {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        password: this.deleteModal.password
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.showToast('Account deleted successfully');
                    setTimeout(() => {
                        window.location.href = "{{ route('login') }}";
                    }, 2000);
                } else {
                    this.showToast(data.message || 'Failed to delete account', 'error');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Failed to delete account', 'error');
            }
        },

        // Other Action Functions
        openPrivacySettings() {
            this.showToast('Privacy settings feature coming soon');
        },

        openNotificationSettings() {
            this.showToast('Notification settings feature coming soon');
        },

        async sendVerification(field) {
            try {
                await fetch("{{ route('api.profile.send-otp') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        field: field,
                        value: this.profile[field]
                    })
                });
                this.startOtpVerification(field, this.profile[field]);
            } catch (e) {
                console.error(e);
                this.showToast('Failed to send verification', 'error');
            }
        },

        // Helper Functions
        showToast(message, type = 'success') {
            clearTimeout(this.toast.timeout);
            this.toast = { 
                show: true, 
                message, 
                type 
            };
            this.toast.timeout = setTimeout(() => this.toast.show = false, 5000);
        },

        getInitials() {
            const fullName = this.getFullName();
            if (!fullName.trim() || fullName === 'Not set') return '?';
            
            // Get initials from full name
            const names = fullName.trim().split(' ');
            if (names.length === 0) return '?';
            
            // Take first letter of first name and last name
            const firstInitial = names[0].charAt(0);
            const lastInitial = names.length > 1 ? names[names.length - 1].charAt(0) : '';
            
            return (firstInitial + lastInitial).toUpperCase();
        },

        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        formatShortDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short'
            });
        },

        formatFieldName(field) {
            const names = {
                full_name: 'Full Name',
                email: 'Email',
                phone: 'Phone',
                gender: 'Gender'
            };
            return names[field] || field;
        }
    };
}
</script>
</body>
</html>
@endsection