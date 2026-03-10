<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingLifecycleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderCalendarController extends Controller
{
    public function index()
    {
        return view('Providers.schedule');
    }

    public function events(Request $request, BookingLifecycleService $bookingLifecycleService): JsonResponse
    {
        $providerProfile = $request->user()?->providerProfile;

        if (!$providerProfile) {
            return response()->json([]);
        }

        $baseQuery = Booking::query()->whereHas('service', function ($query) use ($providerProfile) {
            $query->where('provider_id', $providerProfile->provider_id);
        });

        $bookingLifecycleService->expireStaleBookings($baseQuery);

        $bookings = (clone $baseQuery)
            ->with(['service', 'user'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        $events = $bookings->map(function (Booking $booking) {
            $durationMinutes = max((int) (optional($booking->service)->min_duration ?? 60), 30);

            $datePart = Carbon::parse($booking->booking_date)->format('Y-m-d');
            $timePart = Carbon::parse($booking->start_time)->format('H:i:s');
            $start = Carbon::createFromFormat('Y-m-d H:i:s', $datePart . ' ' . $timePart);
            $end = (clone $start)->addMinutes($durationMinutes);

            $color = $booking->isExpired()
                ? '#6b7280'
                : match ($booking->status) {
                    Booking::STATUS_CONFIRMED => '#f97316',
                    Booking::STATUS_IN_PROGRESS => '#6366f1',
                    Booking::STATUS_COMPLETED => '#10b981',
                    Booking::STATUS_CANCELLED => '#ef4444',
                    default => '#9ca3af',
                };

            return [
                'id' => $booking->id,
                'title' => optional($booking->service)->title ?? 'Service Booking',
                'start' => $start->toIso8601String(),
                'end' => $end->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'status' => $booking->status,
                    'status_label' => $booking->status_label,
                    'is_expired' => $booking->isExpired(),
                    'customer_name' => optional($booking->user)->full_name ?? 'N/A',
                    'phone' => optional($booking->user)->phone ?? 'N/A',
                    'address' => $booking->address ?? 'N/A',
                    'notes' => $booking->notes ?? 'No notes provided',
                    'price' => number_format((float) $booking->total_price, 2, '.', ''),
                    'duration_minutes' => $durationMinutes,
                ],
            ];
        });

        return response()->json($events);
    }

    public function updateStatus(Request $request, string $id, BookingLifecycleService $bookingLifecycleService): JsonResponse
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
            ->with('service')
            ->firstOrFail();

        $booking = $bookingLifecycleService->syncBooking($booking);

        $nextStatus = $validated['status'];

        $isValidTransition = match ($booking->status) {
            Booking::STATUS_PENDING => in_array($nextStatus, [Booking::STATUS_CONFIRMED, Booking::STATUS_CANCELLED], true),
            Booking::STATUS_CONFIRMED => in_array($nextStatus, [Booking::STATUS_IN_PROGRESS, Booking::STATUS_CANCELLED], true),
            Booking::STATUS_IN_PROGRESS => $nextStatus === Booking::STATUS_COMPLETED,
            default => false,
        };

        if (!$isValidTransition) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid booking status transition.',
                'current_status' => $booking->status,
                'status_label' => $booking->status_label,
            ], 422);
        }

        $payload = ['status' => $nextStatus];
        if ($nextStatus === Booking::STATUS_CANCELLED) {
            $payload['cancellation_reason'] = Booking::CANCELLATION_REASON_PROVIDER;
            $payload['cancelled_at'] = now();
        }

        $booking->update($payload);

        return response()->json([
            'success' => true,
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'status_label' => $booking->fresh()?->status_label ?? $booking->status_label,
        ]);
    }
}