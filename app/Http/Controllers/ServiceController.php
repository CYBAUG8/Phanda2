<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([

            'provider_id'      => 'required|exists:provider_profiles,provider_id',
            'title'            => 'required|string',
            'description'      => 'required|string',
            'base_price'       => 'required|numeric',
            'min_duration'     => 'required|integer',
            'location'         => 'required|string',
        ]);

        $service = Service::create([
            'category_id'      =>  $request->category_id,
            'provider_id'      => $request->provider_id,
            'provider_name'    => $request->provider_name ?? 'Test Provider',
            'title'            => $request->title,
            'description'      => $request->description,
            'base_price'       => $request->base_price,
            'min_duration'     => $request->min_duration,
            'location'         => $request->location,
            'rating'           => 0,
            'reviews_count'    => 0,
            'image'            => null,
            'is_active'        => true,
        ]);

        return response()->json([
            'message' => 'Service created successfully',
            'data' => $service
        ]);
    }
}
