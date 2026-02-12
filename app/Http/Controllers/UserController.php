<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class UserController extends Controller
{
    public function getUserInfo(Request $request)
    {
        $user = auth()->user(); 

        return response()->json([
            'user' => [
                'full_name' => $user->full_name,
                'email'    => $user->email,
                'phone'    => $user->phone,
            ]
        ]);
    }

    public function updateUserInfo(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'field' => 'required|string',
            'value' => 'required|string',
        ]);

        if ($request->field == 'full_name') {
            $user->full_name = $request->input('value');
        } elseif ($request->field == 'email' || $request->field == 'phone') {
             
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
            
        $user->save();
        
        return response()->json([
            'message' => 'User information updated successfully',
            'user' => $user
        ]);
    }

    // Send OTP for verification
    public function sendOtp(Request $request)
    {
        $user = $request->user();

        $otp = rand(100000, 999999);

        Cache::put(
            "otp_{$user->user_id}",
            $otp,
            now()->addMinutes(10)
        );
        
        return response()->json([
            'message' => 'OTP sent successfully',
            'otp' => $otp // In production, send via SMS/Email instead
        ]);
    }

    // Update password
     public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Check if new password is same as current password
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'message' => 'New password must be different from current password'
            ], 400);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }

    // Delete account
    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        
        // Optional: Validate password for extra security
        $request->validate([
            'password' => 'required|string',
        ]);
        
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Password is incorrect'
            ], 400);
        }
        
        // Soft delete if using soft deletes
        $user->delete();
        
        Auth::logout();
        
        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }
    
}