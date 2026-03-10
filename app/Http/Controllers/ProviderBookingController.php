<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class ProviderBookingController extends Controller
{
    public function index(Request $request)
    {
        $providerProfile = $request->user()->providerProfile;
        abort_if(!$providerProfile, 403, 'Provider profile not found.');

        $bookings = Booking::whereHas('service', function ($query) use ($providerProfile) {
                $query->where('provider_id', $providerProfile->provider_id);
            })
            ->with(['user', 'service'])
            ->orderByDesc('created_at')
            ->get();

        return view('Providers.bookings', compact('bookings'));
    }

    public function confirm(Request $request, string $id)
    {
        $booking = $this->findProviderBooking($request, $id);

        if ($booking->status !== 'pending') {
            return $this->statusResponse($request, $booking, false, 'Only pending bookings can be confirmed.');
        }

        $booking->update(['status' => 'confirmed']);

        return $this->statusResponse($request, $booking, true, 'Booking confirmed.');
    }

    public function start(Request $request, string $id)
    {
        $booking = $this->findProviderBooking($request, $id);

        if ($booking->status !== 'confirmed') {
            return $this->statusResponse($request, $booking, false, 'Booking must be confirmed first.');
        }

        $booking->update(['status' => 'in_progress']);

        return $this->statusResponse($request, $booking, true, 'Booking is now in progress.');
    }

    public function complete(Request $request, string $id)
    {
        $booking = $this->findProviderBooking($request, $id);

        if ($booking->status !== 'in_progress') {
            return $this->statusResponse($request, $booking, false, 'Only in-progress bookings can be completed.');
        }

        $booking->update(['status' => 'completed']);

        return $this->statusResponse($request, $booking, true, 'Booking marked as completed.');
    }

    public function cancel(Request $request, string $id)
    {
        $booking = $this->findProviderBooking($request, $id);

        if (!in_array($booking->status, ['pending', 'confirmed'], true)) {
            return $this->statusResponse($request, $booking, false, 'This booking cannot be cancelled.');
        }

        $booking->update(['status' => 'cancelled']);

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

    private function statusResponse(Request $request, Booking $booking, bool $success, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'status' => $booking->status,
                'message' => $message,
                'booking_id' => $booking->id,
            ], $success ? 200 : 422);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
<<<<<<< HEAD
=======

>>>>>>> services-bookings-feature
