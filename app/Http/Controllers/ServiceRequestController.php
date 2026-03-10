<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\BookingCreationService;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

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

        return response()->json([
            'message' => 'Service request sent successfully',
            'data' => $booking,
        ]);
    }
}

