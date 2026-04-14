<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;

class ProviderCalendarController extends Controller
{
    public function index()
    {
        return view('providers.schedule');
    }

    public function events()
    {
        $user = Auth::user();

        if (!$user || !$user->providerProfile) {
            return response()->json([]);
        }

        $providerId = $user->providerProfile->provider_id;

        $serviceRequests = ServiceRequest::with(['service', 'customer'])
            ->where('provider_id', $providerId)
            ->get();

        $events = $serviceRequests->map(function ($request) {

            $color = match ($request->status) {
                'confirmed' => '#f97316',
                'completed' => '#10b981',
                'cancelled' => '#ef4444',
                default     => '#9ca3af',
            };

            return [
                'id'    => $request->id,
                'title' => $request->service->name ?? 'Service Request',
                'start' => $request->scheduled_at ?? null,
                'end'   => $request->end_time ?? null,
                'color' => $color,
            ];
        });

        return response()->json($events);
    }
}