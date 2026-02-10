<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    // Update password
    public function updatePassword(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => 'required|min:6|confirmed',
                'new_password_confirmation' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Verify current password
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            // Check if new password is same as current
            if (Hash::check($request->input('new_password'), $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New password must be different from current password'
                ], 422);
            }

            // Check password strength
            $password = $request->input('new_password');
            if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password must include both letters and numbers'
                ], 422);
            }

            // Update password
            $user->password = Hash::make($password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Update password error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password'
            ], 500);
        }
    }

    // Delete account
    public function destroy()
    {
        try {
            $user = Auth::user();
            
            // Soft delete the user
            $user->delete();

            // Revoke all tokens
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete account error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account'
            ], 500);
        }
    }

    // Export user data
    public function exportData()
    {
        try {
            $user = Auth::user();
            
            $data = [
                'user_info' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'gender' => $user->gender,
                    'language' => $user->language,
                ],
                'settings' => $user->settings ? $user->settings->toArray() : [],
                'locations' => $user->locations->toArray(),
                'emergency_contact' => $user->emergencyContact ? $user->emergencyContact->toArray() : null,
                'login_history' => $user->loginHistories()->recent(30)->get()->toArray(),
                'exported_at' => now()->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'download_url' => $this->generateDownloadLink($data)
            ]);

        } catch (\Exception $e) {
            Log::error('Export data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data'
            ], 500);
        }
    }

    private function generateDownloadLink($data)
    {
        // In a real application, you would:
        // 1. Store the JSON file in storage
        // 2. Generate a signed URL
        // 3. Return the URL for download
        
        // For simplicity, we'll return a base64 encoded data URL
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $base64 = base64_encode($json);
        
        return 'data:application/json;base64,' . $base64;
    }

    // Update preferred contact method
    public function updateContactMethod(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validator = Validator::make($request->all(), [
                'method' => 'required|in:Call,SMS,WhatsApp,Email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $user->preferred_contact_method = $request->input('method');
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Preferred contact method updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Update contact method error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update contact method'
            ], 500);
        }
    }

    // Toggle data sharing
    public function toggleDataSharing(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validator = Validator::make($request->all(), [
                'enabled' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $user->data_sharing_enabled = $request->input('enabled');
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Data sharing preference updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Toggle data sharing error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update data sharing preference'
            ], 500);
        }
    }
}