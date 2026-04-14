<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        $user = $request->user();

        $totalRequests = Booking::where('user_id', $user->user_id)->count();
        $activeRequests = Booking::where('user_id', $user->user_id)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->count();
        $completedRequests = Booking::where('user_id', $user->user_id)
            ->where('status', 'completed')
            ->count();

        $profileData = [
            'full_name' => $user->full_name,
            'first_name' => $this->extractFirstName($user->full_name),
            'last_name' => $this->extractLastName($user->full_name),
            'email' => $user->email,
            'email_verified' => $user->email_verified_at !== null,
            'phone' => $user->phone,
            'phone_verified' => !empty($user->phone_verified_at),
            'gender' => $user->gender ?? null,
            'role' => $user->role ?? 'customer',
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'member_id' => $user->member_id ?? ('PAN-' . strtoupper(substr(str_replace('-', '', $user->user_id), 0, 6))),
            'account_status' => $user->account_status ?? 'active',
            

            'total_requests' => $user->bookings()->count(),
            'active_requests' => $user->bookings()->whereIn('status', ['pending', 'accepted'])->count(),
            'completed_requests' => $user->bookings()->where('status', 'completed')->count(),
            
          
            'addresses' => $user->addresses()->get()->map(function ($address) {
                return [
                    'address_id' => $address->address_id,
                    'type' => $address->type,
                    'street' => $address->street,
                    'city' => $address->city,
                    'province' => $address->province,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                    'is_default' => (bool) $address->is_default,
                ];
            })->toArray(),
        ];

        return response()->json([
            'success' => true,
            'profile' => $profileData,
        ]);
    }

    private function extractFirstName(?string $fullName): string
    {
        if (!$fullName) {
            return '';
        }

        $parts = explode(' ', trim($fullName));
        return $parts[0] ?? '';
    }

    private function extractLastName(?string $fullName): string
    {
        if (!$fullName) {
            return '';
        }

        $parts = explode(' ', trim($fullName));
        return count($parts) > 1 ? $parts[count($parts) - 1] : '';
    }

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
                'errors' => $validator->errors(),
            ], 422);
        }

        $field = $request->input('field');
        $value = $request->input('value');
        
        // Map field names if needed
        $dbField = $field;
        if ($field === 'first_name' || $field === 'last_name') {
           
       
            if ($field === 'first_name') {
                $user->first_name = $value;
            } else {
                $user->last_name = $value;
            }
           
            $fullName = ($user->first_name ?? '') . ' ' . ($user->last_name ?? '');
            $user->full_name = trim($fullName);

        } elseif ($field === 'gender') {
            $user->userProfile->gender = $value;
        } else {
            // For other fields, check if they exist on the user model
            if (in_array($field, ['email', 'phone'])) {
            $request->validate([
                'otp' => 'required|digits:6',
            ]);

            $cachedOtp = Cache::get("otp_{$user->user_id}");

            if (!$cachedOtp || $cachedOtp != $request->otp) {
                return response()->json([
                    'message' => 'Invalid or expired OTP',
                ], 400);
            }
           
            Cache::forget("otp_{$user->user_id}");
            
            if ($request->field == 'email') {
                $user->email = $request->input('value');
            } else {
                $user->phone = $request->input('value');
            }
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
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage(),
            ], 500);
        }
    }

   

     public function sendOtp(Request $request)
    {
        $user = $request->user();

        $request->validate([
        'field' => 'required|in:email,phone',
        'value' => 'required|string'
        ]);

        $otp = rand(100000, 999999);

        Cache::put(
            "otp_{$user->user_id}",
            $otp,
            now()->addMinutes(10)
        );

   

        if ($request->field === 'email') {
           Mail::to($request->value)->send(new OtpMail($otp));
        }
if ($request->field === 'phone') {
        try {
            $twilio = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $twilio->messages->create(
                $request->value,
                [
                    'from' => config('services.twilio.from'),
                    'body' => "Your Phanda verification code is: {$otp}. It expires in 10 minutes. Do not share this code with anyone.",
                ]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send OTP SMS', [
                'user_id' => $user->user_id,
                'phone'   => $request->value,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to send OTP via SMS. Please try again.',
            ], 500);
        }
    }

         return response()->json([
            'message' => 'OTP sent successfully',
        ]);
        

    }

    
    public function getAddresses(Request $request)
    {
        return response()->json([
            'success' => true,
            'addresses' => $request->user()->addresses()->get(),
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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->boolean('is_default')) {
            $user->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        $address = Address::create(array_merge(
            $request->only(['type', 'street', 'city', 'province', 'postal_code', 'country', 'latitude', 'longitude', 'is_default']),
            ['user_id' => $user->user_id]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully',
            'address' => $address,
        ]);
    }

    public function updateAddress(Request $request, $id)
    {
        $user = $request->user();
        $address = $user->addresses()->find($id);

        if (!$address) {
            return response()->json(['success' => false, 'message' => 'Address not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|required|in:home,work,billing,shipping,other',
            'street' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'province' => 'sometimes|required|string|max:100',
            'postal_code' => 'sometimes|required|string|max:20',
            'country' => 'sometimes|required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_default' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        if ($request->boolean('is_default')) {
            $user->addresses()->where('is_default', true)->where('address_id', '!=', $id)->update(['is_default' => false]);
        }

        $address->update($request->only(['type', 'street', 'city', 'province', 'postal_code', 'country', 'latitude', 'longitude', 'is_default']));

        return response()->json(['success' => true, 'message' => 'Address updated successfully', 'address' => $address]);
    }

    public function destroyAddress(Request $request, $id)
    {
        $user = $request->user();
        $address = $user->addresses()->find($id);

        if (!$address) {
            return response()->json(['success' => false, 'message' => 'Address not found'], 404);
        }

        $isDefault = (bool) $address->is_default;
        $address->delete();

        if ($isDefault) {
            $newDefault = $user->addresses()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Address deleted successfully']);
    }

    public function setDefaultAddress(Request $request, $id)
    {
        $user = $request->user();
        $address = $user->addresses()->find($id);

        if (!$address) {
            return response()->json(['success' => false, 'message' => 'Address not found'], 404);
        }

        $user->addresses()->where('is_default', true)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json(['success' => true, 'message' => 'Default address updated successfully']);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Password is required'], 422);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect password'], 400);
        }

        $user->delete();
        Auth::logout();

        return response()->json(['success' => true, 'message' => 'Account deleted successfully']);
    }
}

