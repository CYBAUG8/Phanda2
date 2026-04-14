<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use App\Models\ProviderProfile;
use App\Models\Category;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $providerProfile = ProviderProfile::where('provider_id', $user->id)->first();
        $categories = Category::all();
        $services = Service::where('provider_id', $user->id)->with('category')->get();

        return view('Providers.services', compact('providerProfile', 'categories', 'services'));
    }    
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $providerProfile = $user->providerProfile;
        if (!$providerProfile) {
            return response()->json(['message' => 'Provider profile not found.'], 403);
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'base_price' => 'required|numeric|min:0',
            'min_duration' => 'required|integer|min:15|max:1440',
            'location' => 'required|string|max:255',
        ]);

        $service = Service::create([
            'category_id' => $request->category_id,
            'provider_id' => $providerProfile->provider_id,
            'provider_name' => $providerProfile->business_name ?: $user->full_name,
            'title' => $request->title,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'min_duration' => $request->min_duration,
            'location' => $request->location,
            'rating' => 0,
            'reviews_count' => 0,
            'image' => null,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Service created successfully',
            'data' => $service,
        ], 201);
    }
}
