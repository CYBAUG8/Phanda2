<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmergencyContact;

class EmergencyContactController extends Controller
{
    /**
     * Get current user's emergency contact
     */
    public function show(Request $request)
    {
        return response()->json([
            'emergency_contact' => $request->user()->emergencyContact
        ]);
    }

    /**
     * Create or update emergency contact
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:160',
            'phone'        => 'required|string|max:32',
            'relationship' => 'nullable|string|max:50',
        ]);

        $user = $request->user();

        $contact = EmergencyContact::updateOrCreate(
            ['user_id' => $user->user_id],
            $validated
        );

        return response()->json([
            'message' => 'Emergency contact saved successfully',
            'emergency_contact' => $contact
        ]);
    }

    /**
     * Delete emergency contact
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        if ($user->emergencyContact) {
            $user->emergencyContact->delete();
        }

        return response()->json([
            'message' => 'Emergency contact removed'
        ]);
    }
}