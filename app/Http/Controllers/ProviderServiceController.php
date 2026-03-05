<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderServiceController extends Controller
{
    public function index() {
        $providerId = Auth::user()->providerProfile->provider_id;

        $services = Service::where('provider_id', $providerId)->get();

        return view('providers.services', compact('services'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0'
        ]);

        $providerId = Auth::user()->providerProfile->provider_id;

        Service::create([
            'provider_id' => $providerId,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'active' => true
        ]);

        return back()->with('success', 'Service added successfully.');
    }

    public function toggle(Service $service) {
        $service->active = !$service->active;
        $service->save();

        return response()->json([
            'success' => true,
            'active' => $service->active
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $providerId = Auth::user()->providerProfile->provider_id;

        if ($service->provider_id !== $providerId) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0'
        ]);

        $service->update($request->only('name', 'description', 'price'));

        return response()->json([
            'success' => true,
            'service' => $service
        ]);
    }

    public function destroy(Service $service) {
        $service->delete();
        return response()->json(['success' => true]);
    }
}