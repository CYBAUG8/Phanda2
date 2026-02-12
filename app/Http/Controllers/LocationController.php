<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    // Get all saved locations
    public function index()
    {
        try {
            $user = Auth::user();
            $locations = $user->locations()->orderBy('is_default', 'desc')->get();

            return response()->json([
                'success' => true,
                'locations' => $locations
            ]);
        } catch (\Exception $e) {
            Log::error('Get locations error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch locations'
            ], 500);
        }
    }

    // Create new location
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'address' => 'required|string',
                'type' => 'required|in:home,work,other',
                'is_default' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $data = $request->only(['name', 'address', 'type', 'is_default']);
            $data['user_id'] = $user->user_id;

            // If setting as default, unset other defaults
            if ($request->input('is_default', false)) {
                $user->locations()->where('is_default', true)->update(['is_default' => false]);
            }

            $location = Location::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Location saved successfully',
                'location' => $location
            ]);

        } catch (\Exception $e) {
            Log::error('Store location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save location'
            ], 500);
        }
    }

    // Update location
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $location = $user->locations()->find($id);

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'address' => 'sometimes|required|string',
                'type' => 'sometimes|required|in:home,work,other',
                'is_default' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // If setting as default, unset other defaults
            if ($request->has('is_default') && $request->input('is_default')) {
                $user->locations()->where('is_default', true)->update(['is_default' => false]);
            }

            $location->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
                'location' => $location
            ]);

        } catch (\Exception $e) {
            Log::error('Update location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location'
            ], 500);
        }
    }

    // Delete location
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $location = $user->locations()->find($id);

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found'
                ], 404);
            }

            $location->delete();

            // If deleted location was default, set another as default
            if ($location->is_default) {
                $newDefault = $user->locations()->first();
                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Location deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete location'
            ], 500);
        }
    }

    // Set default location
    public function setDefault($id)
    {
        try {
            $user = Auth::user();
            $location = $user->locations()->find($id);

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found'
                ], 404);
            }

            // Unset all other defaults
            $user->locations()->where('is_default', true)->update(['is_default' => false]);

            // Set this as default
            $location->update(['is_default' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Default location updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Set default location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to set default location'
            ], 500);
        }
    }
}