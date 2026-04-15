<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\BookingCreationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceRequestController extends Controller
{
    public function store(Request $request, BookingCreationService $bookingCreationService)
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'service_id' => ['required', Rule::exists('services', 'service_id')->whereNull('deleted_at')],
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'address' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $service = Service::where('service_id', $validated['service_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $booking = $bookingCreationService->createFromService($user, $service, $validated);
        $booking = $booking->fresh(['service']);

        return response()->json([
            'message' => 'Service request sent successfully',
            'data' => array_merge($booking->toArray(), [
                'status_label' => $booking->status_label,
                'display_status' => $booking->display_status,
            ]),
        ]);
    }
}