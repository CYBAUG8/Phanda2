<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,service_id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'address' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $service = Service::where('service_id', $validated['service_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $booking = Booking::create([
            'user_id' => $request->user()->user_id,
            'service_id' => $service->service_id,
            'booking_date' => $validated['booking_date'],
            'start_time' => $validated['start_time'],
            'status' => 'pending',
            'total_price' => $service->base_price,
            'address' => $validated['address'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Service request sent successfully',
            'data' => $booking,
        ]);
    }
}