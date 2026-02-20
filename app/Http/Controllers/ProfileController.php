<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserProfile;
use App\Models\Address;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
   
    public function getProfile(Request $request)
    {
        $user = $request->user();
        
        // Format user data for profile page
        $profileData = [
            'first_name' => $user->first_name ?? $this->extractFirstName($user->full_name),
            'last_name' => $user->last_name ?? $this->extractLastName($user->full_name),
            'email' => $user->email,
            'email_verified' => $user->email_verified_at !== null,
            'phone' => $user->phone,
            'phone_verified' => $user->phone_verified_at !== null,
            'gender' => $user->gender,
            'role' => $user->role ?? 'Customer',
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'member_id' => $user->member_id ?? 'PAN-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
            'account_status' => $user->account_status ?? 'active',
            
            // Mock service request stats (you would replace with actual queries)
            'total_requests' => 0,
            'active_requests' => 0,
            'completed_requests' => 0,
            
            // Addresses
            'addresses' => $user->addresses()->get()->map(function ($address) {
                return [
                    'address_id' => $address->address_id,
                    'type' => $address->type,
                    'street' => $address->street,
                    'city' => $address->city,
                    'province' => $address->province,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country,
                    'is_default' => $address->is_default,
                ];
            })->toArray(),
        ];
        
        return response()->json([
            'success' => true,
            'profile' => $profileData
        ]);
    }
    
    /**
     * Helper method to extract first name from full_name
     */
    private function extractFirstName($fullName)
    {
        if (!$fullName) return '';
        $parts = explode(' ', $fullName);
        return $parts[0] ?? '';
    }
    
    /**
     * Helper method to extract last name from full_name
     */
    private function extractLastName($fullName)
    {
        if (!$fullName) return '';
        $parts = explode(' ', $fullName);
        return count($parts) > 1 ? $parts[count($parts) - 1] : '';
    }

    /**
     * Update profile information
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'field' => 'required|string',
            'value' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $field = $request->input('field');
        $value = $request->input('value');
        
        // Map field names if needed
        $dbField = $field;
        if ($field === 'first_name' || $field === 'last_name') {
            // Handle updating first_name/last_name
            if ($field === 'first_name') {
                $user->first_name = $value;
            } else {
                $user->last_name = $value;
            }
            // Also update full_name
            $fullName = ($user->first_name ?? '') . ' ' . ($user->last_name ?? '');
            $user->full_name = trim($fullName);
        } elseif ($field === 'gender') {
            $user->gender = $value;
        } else {
            // For other fields, check if they exist on the user model
            if (in_array($field, ['email', 'phone'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email and phone require OTP verification'
                ], 400);
            }
            
            // Only update if the field exists
            if (in_array($field, $user->getFillable())) {
                $user->$field = $value;
            }
        }
        
        try {
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'profile' => $this->getProfileData($user)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

   

    public function sendOtp(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'field' => 'required|in:email,phone',
            'value' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $field = $request->input('field');
        $value = $request->input('value');
  

        $otp = rand(100000, 999999);
        
        
        $cacheKey = "profile_otp_{$user->user_id}_{$field}";
        Cache::put($cacheKey, [
            'otp' => $otp,
            'value' => $value
        ], now()->addMinutes(10));
        
       
        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
            'otp' => $otp 
        ]);
    }

    
    public function updateWithOtp(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'field' => 'required|in:email,phone',
            'value' => 'required',
            'otp' => 'required|digits:6',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $field = $request->input('field');
        $value = $request->input('value');
        $otp = $request->input('otp');
       

        $cacheKey = "profile_otp_{$user->user_id}_{$field}";
        $cachedData = Cache::get($cacheKey);
        
        if (!$cachedData || $cachedData['otp'] != $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }
        

        if ($cachedData['value'] != $value) {
            return response()->json([
                'success' => false,
                'message' => 'Value does not match OTP request'
            ], 400);
        }
        

        if ($field === 'email') {
            $user->email = $value;
            $user->email_verified_at = now();
        } elseif ($field === 'phone') {
            $user->phone = $value;
            $user->phone_verified_at = now();
        }
        
        try {
            $user->save();
           

            Cache::forget($cacheKey);
            
            return response()->json([
                'success' => true,
                'message' => 'Profile updated and verified successfully',
                'profile' => $this->getProfileData($user)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function getAddresses(Request $request)
    {
        $user = $request->user();
        $addresses = $user->addresses()->get();
        
        return response()->json([
            'success' => true,
            'addresses' => $addresses
        ]);
    }

    public function storeAddress(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:home,work,billing,shipping,other',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_default' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // If setting as default, unset other defaults
        if ($request->input('is_default', false)) {
            $user->addresses()->where('is_default', true)->update(['is_default' => false]);
        }
        
        $addressData = $request->only(['type', 'street', 'city', 'province', 'postal_code', 'country', 'is_default']);
        $addressData['user_id'] = $user->user_id;
        
        try {
            $address = Address::create($addressData);
            
            return response()->json([
                'success' => true,
                'message' => 'Address added successfully',
                'address' => $address
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add address: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function updateAddress(Request $request, $id)
    {
        $user = $request->user();
        
        $address = $user->addresses()->find($id);
        
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|required|in:home,work,billing,shipping,other',
            'street' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'province' => 'sometimes|required|string|max:100',
            'postal_code' => 'sometimes|required|string|max:20',
            'country' => 'sometimes|required|string|max:100',
            'is_default' => 'sometimes|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
       
        if ($request->has('is_default') && $request->input('is_default')) {
            $user->addresses()->where('is_default', true)->where('address_id', '!=', $id)->update(['is_default' => false]);
        }
        
        try {
            $address->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully',
                'address' => $address
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update address: ' . $e->getMessage()
            ], 500);
        }
    }

  
    public function destroyAddress(Request $request, $id)
    {
        $user = $request->user();
        
        $address = $user->addresses()->find($id);
        
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }
        
        try {
            $address->delete();
            
           
            if ($address->is_default) {
                $newDefault = $user->addresses()->first();
                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete address: ' . $e->getMessage()
            ], 500);
        }
    }

  
    public function setDefaultAddress(Request $request, $id)
    {
        $user = $request->user();
        
        $address = $user->addresses()->find($id);
        
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }
        
        try {
        

            $user->addresses()->where('is_default', true)->update(['is_default' => false]);
            
            
            $address->update(['is_default' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Default address updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set default address: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Password is required'
            ], 422);
        }
        

        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password'
            ], 400);
        }
        
        try {
          
            $user->delete();
            
           
            Auth::logout();
            
            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account: ' . $e->getMessage()
            ], 500);
        }
    }

   
    private function getProfileData($user)
    {
        return [
            'first_name' => $user->first_name ?? $this->extractFirstName($user->full_name),
            'last_name' => $user->last_name ?? $this->extractLastName($user->full_name),
            'email' => $user->email,
            'email_verified' => $user->email_verified_at !== null,
            'phone' => $user->phone,
            'phone_verified' => $user->phone_verified_at !== null,
            'gender' => $user->gender,
            'role' => $user->role ?? 'Customer',
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'member_id' => $user->member_id ?? 'PAN-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
            'account_status' => $user->account_status ?? 'active',
        ];
    }
}