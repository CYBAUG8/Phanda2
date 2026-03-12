<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function getSettings(Request $request)
    {
        $user = $request->user();

        $settings = $user->settings()->firstOrCreate([
            'user_id' => $user->user_id,
        ]);

        return response()->json([
            'settings' => $settings,
        ]);
    }

    public function toggleSettings(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
        ]);

        $allowed = [
            'notifications',
            'two_factor_auth',
            'auto_share',
            'repeat_providers',
            'same_gender_provider',
        ];

        if (!in_array($request->key, $allowed, true)) {
            return response()->json(['message' => 'Invalid setting'], 422);
        }

        $user = $request->user();
        $settings = $user->settings()->firstOrCreate([
            'user_id' => $user->user_id,
        ]);

        $settings->update([
            $request->key => filter_var($request->value, FILTER_VALIDATE_BOOLEAN),
        ]);

        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => $settings->fresh(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        return $this->toggleSettings($request);
    }

    public function updateNotificationPreferences(Request $request)
    {
        $request->validate([
            'notifications' => 'required|boolean',
        ]);

        $request->merge([
            'key' => 'notifications',
            'value' => $request->boolean('notifications'),
        ]);

        return $this->toggleSettings($request);
    }
}
