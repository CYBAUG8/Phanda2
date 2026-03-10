<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderCalendarController extends Controller
{
    //
    public function index(){

      return view('providers.schedule');
    }




    public function events()
{
    $user = Auth::user();
    if (!$user || !$user->providerProfile) {
        return response()->json([]); // or return error response
    }

    $providerId = $user->providerProfile->provider_id;

    $serviceRequests = ServiceRequest::with(['service', 'customer'])
        ->where('provider_id', $providerId)
        ->get();

        $events = $bookings->map(function (Booking $booking) {
            $durationMinutes = max((int) (optional($booking->service)->min_duration ?? 60), 30);

            $datePart = Carbon::parse($booking->booking_date)->format('Y-m-d');
            $timePart = Carbon::parse($booking->start_time)->format('H:i:s');
            $start = Carbon::createFromFormat('Y-m-d H:i:s', $datePart . ' ' . $timePart);
            $end = (clone $start)->addMinutes($durationMinutes);

            $color = match ($booking->status) {
                'confirmed' => '#f97316',
                'in_progress' => '#6366f1',
                'completed' => '#10b981',
                'cancelled' => '#ef4444',
                default => '#9ca3af',
            };

       

        

        return [
            'id'               => $request->booking_id,
            'title'            => $request->service->title,
            'start' => date('Y-m-d', strtotime($request->booking_date))
                        . 'T' .
                        date('H:i:s', strtotime($request->start_time)),

            'end'   => date('Y-m-d', strtotime($request->booking_date))
                        . 'T' .
                        date('H:i:s', strtotime($request->end_time)),
            'backgroundColor'  => $color,
            'borderColor'      => $color,
            'textColor'        => '#ffffff',
            'extendedProps'    => [
                'customer_name' => $request->customer->full_name ?? 'N/A',
                'customer_id'   => $request->customer->user_id ?? null,
                'phone'         => $request->customer->phone ?? 'N/A',
                'address'       => $request->customer->address ?? $request->address, 
                'status'        => $request->status,
                'price'         => $request->total_price,
            ]
        ];
    });

        return response()->json($events);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,in_progress,completed,cancelled',
        ]);

        $providerProfile = $request->user()?->providerProfile;
        abort_if(!$providerProfile, 403, 'Provider profile not found.');

        $booking = Booking::query()
            ->where('id', $id)
            ->whereHas('service', function ($query) use ($providerProfile) {
                $query->where('provider_id', $providerProfile->provider_id);
            })
            ->firstOrFail();

        $nextStatus = $validated['status'];

        $isValidTransition = match ($booking->status) {
            'pending' => in_array($nextStatus, ['confirmed', 'cancelled'], true),
            'confirmed' => in_array($nextStatus, ['in_progress', 'cancelled'], true),
            'in_progress' => $nextStatus === 'completed',
            default => false,
        };

        if (!$isValidTransition) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid booking status transition.',
                'current_status' => $booking->status,
            ], 422);
        }

        $booking->update(['status' => $nextStatus]);

        return response()->json([
            'success' => true,
            'booking_id' => $booking->id,
            'status' => $booking->status,
        ]);
    }
}