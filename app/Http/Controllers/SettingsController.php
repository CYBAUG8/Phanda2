<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    //

   public function getSettings(Request $request)
{
    $user = $request->user();

      $settings = $user->settings()->firstOrCreate(
            ['user_id' => $user->user_id],
            
        );

    return response()->json([
        'settings' => $settings
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
        'same_gender_provider'
    ];

    if (!in_array($request->key, $allowed)) {
        return response()->json(['message' => 'Invalid setting'], 422);
    }

    $user = $request->user();

    $user->settings()->update([
        $request->key => filter_var($request->value, FILTER_VALIDATE_BOOLEAN)
    ]);

    return response()->json([
        'message' => 'Settings updated successfully',
        'settings' => $user->settings
    ]);
}

}