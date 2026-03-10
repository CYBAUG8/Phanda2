<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingLifecycleService;
use App\Services\BookingPaymentService;
use Illuminate\Http\Request;

class ProviderBookingController extends Controller
{
    public function index(Request $request, BookingLifecycleService $bookingLifecycleService)
    {
        $providerProfile = $request->user()->providerProfile;
        abort_if(!$providerProfile, 403, 'Provider profile not found.');

        $bookingLifecycleService->expireStaleBookings(
            $this->providerBookingQuery($providerProfile->provider_id)
        );

        $bookings = $this->providerBookingQuery($providerProfile->provider_id)
            ->with(['user', 'service'])
            ->orderByDesc('created_at')
            ->get();

        return view('Providers.bookings', compact('bookings'));
    }

    public function confirm(
        Request $request,
        string $id,
        BookingLifecycleService $bookingLifecycleService,
        BookingPaymentService $bookingPaymentService
    ) {
        $booking = $bookingLifecycleService->syncBooking($this->findProviderBooking($request, $id));

        if ($booking->status !== Booking::STATUS_PENDING) {
            return $this->statusResponse($request, $booking, false, 'Only pending bookings can be confirmed.');
        }

        $booking->update(['status' => Booking::STATUS_CONFIRMED]);
        $bookingPaymentService->markPaymentRequired($booking->fresh() ?? $booking);

        return $this->statusResponse($request, $booking->fresh() ?? $booking, true, 'Booking confirmed. Waiting for user payment.');
    }

    public function start(Request $request, string $id, BookingLifecycleService $bookingLifecycleService)
    {
        $booking = $bookingLifecycleService->syncBooking($this->findProviderBooking($request, $id));

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return $this->statusResponse($request, $booking, false, 'Booking must be confirmed first.');
        }

        if ($booking->payment_status !== Booking::PAYMENT_STATUS_PAID) {
            return $this->statusResponse($request, $booking, false, 'User payment is required before starting this booking.');
        }

        $booking->update(['status' => Booking::STATUS_IN_PROGRESS]);

        return $this->statusResponse($request, $booking, true, 'Booking is now in progress.');
    }

    public function complete(Request $request, string $id, BookingLifecycleService $bookingLifecycleService)
    {
        $booking = $bookingLifecycleService->syncBooking($this->findProviderBooking($request, $id));

        if ($booking->status !== Booking::STATUS_IN_PROGRESS) {
            return $this->statusResponse($request, $booking, false, 'Only in-progress bookings can be completed.');
        }

        $booking->update(['status' => Booking::STATUS_COMPLETED]);

        return $this->statusResponse($request, $booking, true, 'Booking marked as completed.');
    }

    public function cancel(Request $request, string $id, BookingLifecycleService $bookingLifecycleService)
    {
        $booking = $bookingLifecycleService->syncBooking($this->findProviderBooking($request, $id));

        if (!in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED], true)) {
            return $this->statusResponse($request, $booking, false, 'This booking cannot be cancelled.');
        }

        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'cancellation_reason' => Booking::CANCELLATION_REASON_PROVIDER,
            'cancelled_at' => now(),
        ]);

        return $this->statusResponse($request, $booking, true, 'Booking cancelled.');
    }

    private function findProviderBooking(Request $request, string $id): Booking
    {
        $providerProfile = $request->user()->providerProfile;
        abort_if(!$providerProfile, 403, 'Provider profile not found.');

        return Booking::where('id', $id)
            ->whereHas('service', function ($query) use ($providerProfile) {
                $query->where('provider_id', $providerProfile->provider_id);
            })
            ->with(['user', 'service'])
            ->firstOrFail();
    }

    private function providerBookingQuery(string $providerId)
    {
        return Booking::query()->whereHas('service', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        });
    }

    private function statusResponse(Request $request, Booking $booking, bool $success, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
                'cancellation_reason' => $booking->cancellation_reason,
                'status_label' => $booking->status_label,
                'message' => $message,
                'booking_id' => $booking->id,
            ], $success ? 200 : 422);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
