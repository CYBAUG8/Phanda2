<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecoveryContact;
use Illuminate\Support\Facades\Validator;

class RecoveryContactController extends Controller
{
    /**
     * Get current user's recovery contact
     */
    public function show(Request $request)
    {
        $recoveryContact = $request->user()->recoveryContact;
        
        return response()->json([
            'recovery_contact' => $recoveryContact
        ]);
    }

    /**
     * Create or update recovery contact
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:160',
            'phone'        => 'required|string|max:32',
            'email'        => 'nullable|email|max:160',
            'relationship' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        $contact = RecoveryContact::updateOrCreate(
            ['user_id' => $user->user_id],
            $request->only(['name', 'phone', 'email', 'relationship'])
        );

        return response()->json([
            'message' => 'Recovery contact saved successfully',
            'recovery_contact' => $contact
        ]);
    }

    /**
     * Update recovery contact
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:160',
            'phone'        => 'required|string|max:32',
            'email'        => 'nullable|email|max:160',
            'relationship' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $contact = $user->recoveryContact;

        if (!$contact) {
            return response()->json([
                'message' => 'Recovery contact not found'
            ], 404);
        }

        $contact->update($request->only(['name', 'phone', 'email', 'relationship']));

        return response()->json([
            'message' => 'Recovery contact updated successfully',
            'recovery_contact' => $contact
        ]);
    }

    /**
     * Delete recovery contact
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        $contact = $user->recoveryContact;

        if (!$contact) {
            return response()->json([
                'message' => 'Recovery contact not found'
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'message' => 'Recovery contact removed successfully'
        ]);
    }
}