<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderServiceController extends Controller
{
    public function index(Request $request)
    {
        $providerProfile = $request->user()->providerProfile;
        abort_if(!$providerProfile, 403, 'Provider profile not found.');

        $services = Service::with('category')
            ->where('provider_id', $providerProfile->provider_id)
            ->orderByDesc('created_at')
            ->get();

        $categories = Category::orderBy('name')->get();

        return view('Providers.services', compact('services', 'categories', 'providerProfile'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $providerProfile = $user->providerProfile;
        abort_if(!$providerProfile, 403, 'Provider profile not found.');

        $serviceArea = trim((string) ($providerProfile->service_area ?? ''));
        if ($serviceArea === '') {
            return back()->with('error', 'Set your Service Area in Provider Profile before adding a service.');
        }

        $normalized = $this->normalizeServicePayload($request);
        $validator = Validator::make($normalized, [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:65535',
            'base_price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'regex:/^\d{1,8}(\.\d{1,2})?$/'],
            'min_duration' => 'required|integer|min:15|max:1440',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        $providerName = trim((string) ($providerProfile->business_name ?: $user->full_name ?: 'Provider'));

        Service::create([
            'category_id' => $data['category_id'],
            'provider_id' => $providerProfile->provider_id,
            'provider_name' => $providerName,
            'title' => $data['title'],
            'description' => $data['description'],
            'base_price' => $data['base_price'],
            'min_duration' => $data['min_duration'],
            'location' => $serviceArea,
            'is_active' => true,
            'rating' => 0,
            'reviews_count' => 0,
        ]);

        return back()->with('success', 'Service added successfully.');
    }

    public function update(Request $request, Service $service)
    {
        $providerProfile = $request->user()->providerProfile;
        abort_if(!$providerProfile, 403, 'Provider profile not found.');
        abort_if($service->provider_id !== $providerProfile->provider_id, 403, 'Unauthorized');

        $serviceArea = trim((string) ($providerProfile->service_area ?? ''));
        if ($serviceArea === '') {
            return back()->with('error', 'Set your Service Area in Provider Profile before editing a service.');
        }

        $normalized = $this->normalizeServicePayload($request, $service);
        $validator = Validator::make($normalized, [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:65535',
            'base_price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'regex:/^\d{1,8}(\.\d{1,2})?$/'],
            'min_duration' => 'required|integer|min:15|max:1440',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['location'] = $serviceArea;

        $service->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'service' => $service->fresh('category')]);
        }

        return back()->with('success', 'Service updated successfully.');
    }

    public function toggle(Request $request, Service $service)
    {
        $providerProfile = $request->user()->providerProfile;
        abort_if(!$providerProfile, 403, 'Provider profile not found.');
        abort_if($service->provider_id !== $providerProfile->provider_id, 403, 'Unauthorized');

        $service->update(['is_active' => !$service->is_active]);

        $label = $service->is_active ? 'Service resumed.' : 'Service paused.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $service->is_active,
                'message' => $label,
            ]);
        }

        return back()->with('success', $label);
    }

    public function destroy(Request $request, Service $service)
    {
        $providerProfile = $request->user()->providerProfile;
        abort_if(!$providerProfile, 403, 'Provider profile not found.');
        abort_if($service->provider_id !== $providerProfile->provider_id, 403, 'Unauthorized');

        $service->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Service deleted.');
    }

    private function normalizeServicePayload(Request $request, ?Service $existing = null): array
    {
        $title = trim((string) ($request->input('title')
            ?? $request->input('custom_name')
            ?? $request->input('name')
            ?? ($existing?->title ?? '')));

        $description = trim((string) ($request->input('description')
            ?? ($existing?->description ?? '')));

        if ($description === '' && $title !== '') {
            $description = $title;
        }

        return [
            'category_id' => $request->input('category_id')
                ?? $request->input('service_id')
                ?? ($existing?->category_id),
            'title' => $title,
            'description' => $description,
            'base_price' => $request->input('base_price')
                ?? $request->input('price_per_unit')
                ?? $request->input('price')
                ?? ($existing?->base_price),
            'min_duration' => $request->input('min_duration')
                ?? $request->input('duration')
                ?? ($existing?->min_duration)
                ?? 60,
        ];
    }
}


