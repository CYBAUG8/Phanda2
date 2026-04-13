<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
=======
use App\Models\Booking;
use App\Services\BookingLifecycleService;
use App\Services\BookingPaymentService;
use Illuminate\Http\Request;
>>>>>>> feature2
use Illuminate\Support\Facades\Auth;

class ProviderCalendarController extends Controller
{
<<<<<<< HEAD
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

    $events = $serviceRequests->map(function ($request) {
        $color = match ($request->status) {
            'confirmed' => '#f97316',
            'completed' => '#10b981',
            'cancelled' => '#ef4444',
            default     => '#9ca3af',
        };

       

        
=======
    public function index()
    {
        return view('Providers.schedule');
    }

    public function events(BookingLifecycleService $bookingLifecycleService)
    {
        $user = Auth::user();

        if (!$user || !$user->providerProfile) {
            return response()->json([]);
        }

        $providerId = $user->providerProfile->provider_id;

        $bookingQuery = Booking::query()->whereHas('service', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        });

        $bookingLifecycleService->expireStaleBookings($bookingQuery);

        $bookings = (clone $bookingQuery)
            ->with(['service', 'user'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        $events = $bookings->map(function (Booking $booking) use ($bookingLifecycleService) {
            $startAt = $bookingLifecycleService->scheduledStartAt($booking);
            $endAt = $bookingLifecycleService->scheduledEndAt($booking) ?? $startAt?->addMinutes(60);
            $color = $this->statusColor($booking);

            return [
                'id' => $booking->id,
                'title' => $booking->service->title ?? 'Service',
                'start' => $startAt?->format('Y-m-d\\TH:i:s'),
                'end' => $endAt?->format('Y-m-d\\TH:i:s'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'customer_name' => $booking->user->full_name ?? 'N/A',
                    'customer_id' => $booking->user->user_id ?? null,
                    'phone' => $booking->user->phone ?? 'N/A',
                    'address' => $booking->address,
                    'status' => $booking->status,
                    'status_label' => $booking->status_label,
                    'payment_status' => $booking->payment_status,
                    'price' => $booking->total_price,
                    'notes' => $booking->notes,
                    'cancellation_reason' => $booking->cancellation_reason,
                ],
            ];
        });

        return response()->json($events);
    }

    public function updateStatus(
        Request $request,
        string $bookingId,
        BookingLifecycleService $bookingLifecycleService,
        BookingPaymentService $bookingPaymentService
    ) {
        $user = Auth::user();

        if (!$user || !$user->providerProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Provider profile not found.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:confirmed,in_progress,completed,cancelled',
        ]);

        $booking = $bookingLifecycleService->syncBooking(
            $this->findProviderBooking($bookingId, $user->providerProfile->provider_id)
        );

        if ($booking->isExpired()) {
            return response()->json([
                'success' => false,
                'status' => $booking->status,
                'status_label' => $booking->status_label,
                'cancellation_reason' => $booking->cancellation_reason,
                'message' => 'This booking has expired and can no longer be updated.',
            ], 422);
        }

        $targetStatus = $validated['status'];
>>>>>>> feature2

        if ($targetStatus === Booking::STATUS_CONFIRMED) {
            if ($booking->status !== Booking::STATUS_PENDING) {
                return $this->invalidTransitionResponse($booking, 'Only pending bookings can be confirmed.');
            }

            $booking->update([
                'status' => Booking::STATUS_CONFIRMED,
                'cancellation_reason' => null,
                'cancelled_at' => null,
            ]);

            $bookingPaymentService->markPaymentRequired($booking->fresh() ?? $booking);

            return $this->okTransitionResponse($booking->fresh() ?? $booking, 'Booking confirmed. Waiting for user payment.');
        }

        if ($targetStatus === Booking::STATUS_IN_PROGRESS) {
            if ($booking->status !== Booking::STATUS_CONFIRMED) {
                return $this->invalidTransitionResponse($booking, 'Booking must be confirmed first.');
            }

            if ($booking->payment_status !== Booking::PAYMENT_STATUS_PAID) {
                return $this->invalidTransitionResponse($booking, 'User payment is required before starting this booking.');
            }

            $booking->update(['status' => Booking::STATUS_IN_PROGRESS]);

            return $this->okTransitionResponse($booking->fresh() ?? $booking, 'Booking is now in progress.');
        }

        if ($targetStatus === Booking::STATUS_COMPLETED) {
            if ($booking->status !== Booking::STATUS_IN_PROGRESS) {
                return $this->invalidTransitionResponse($booking, 'Only in-progress bookings can be completed.');
            }

            $booking->update(['status' => Booking::STATUS_COMPLETED]);

<<<<<<< HEAD
    return response()->json(['success'=>true]);
   
}

}
=======
            return $this->okTransitionResponse($booking->fresh() ?? $booking, 'Booking marked as completed.');
        }

        if (!in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED], true)) {
            return $this->invalidTransitionResponse($booking, 'This booking cannot be cancelled.');
        }

        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'cancellation_reason' => Booking::CANCELLATION_REASON_PROVIDER,
            'cancelled_at' => now(),
        ]);

        return $this->okTransitionResponse($booking->fresh() ?? $booking, 'Booking cancelled.');
    }

    private function findProviderBooking(string $bookingId, string $providerId): Booking
    {
        return Booking::query()
            ->where('id', $bookingId)
            ->whereHas('service', function ($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })
            ->with(['user', 'service'])
            ->firstOrFail();
    }

    private function statusColor(Booking $booking): string
    {
        if ($booking->isExpired()) {
            return '#64748b';
        }

        return match ($booking->status) {
            Booking::STATUS_CONFIRMED => '#f97316',
            Booking::STATUS_IN_PROGRESS => '#6366f1',
            Booking::STATUS_COMPLETED => '#10b981',
            Booking::STATUS_CANCELLED => '#ef4444',
            default => '#9ca3af',
        };
    }

    private function okTransitionResponse(Booking $booking, string $message)
    {
        return response()->json([
            'success' => true,
            'status' => $booking->status,
            'status_label' => $booking->status_label,
            'payment_status' => $booking->payment_status,
            'cancellation_reason' => $booking->cancellation_reason,
            'message' => $message,
            'booking_id' => $booking->id,
        ]);
    }

    private function invalidTransitionResponse(Booking $booking, string $message)
    {
        return response()->json([
            'success' => false,
            'status' => $booking->status,
            'status_label' => $booking->status_label,
            'payment_status' => $booking->payment_status,
            'cancellation_reason' => $booking->cancellation_reason,
            'message' => $message,
            'booking_id' => $booking->id,
        ], 422);
    }
}

>>>>>>> feature2
