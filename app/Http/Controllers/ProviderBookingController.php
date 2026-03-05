<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class ProviderBookingController extends Controller
{
    /**
     * Display bookings belonging to logged-in provider
     */
    public function index()
    {
        $providerId = Auth::user()->user_id;

        $bookings = Booking::whereHas('service', function ($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })
            ->with(['user', 'service'])
            ->latest()
            ->get();

        return view('providers.bookings', compact('bookings'));
    }

    /**
     * Confirm booking (pending → confirmed)
     */
    public function confirm($id)
    {
        $booking = $this->findProviderBooking($id);

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending bookings can be confirmed.');
        }

        $booking->status = 'confirmed';
        $booking->save();

        return back()->with('success', 'Booking confirmed.');
    }

    /**
     * Start booking (confirmed → in_progress)
     */
    public function start($id)
    {
        $booking = $this->findProviderBooking($id);

        if ($booking->status !== 'confirmed') {
            return back()->with('error', 'Booking must be confirmed first.');
        }

        $booking->status = 'in_progress';
        $booking->save();

        return back()->with('success', 'Booking is now in progress.');
    }

    /**
     * Complete booking (in_progress → completed)
     */
    public function complete($id)
    {
        $booking = $this->findProviderBooking($id);

        if ($booking->status !== 'in_progress') {
            return back()->with('error', 'Only in-progress bookings can be completed.');
        }

        $booking->status = 'completed';
        $booking->save();

        return back()->with('success', 'Booking marked as completed.');
    }

    /**
     * Cancel booking (pending or confirmed → cancelled)
     */
    public function cancel($id)
    {
        $booking = $this->findProviderBooking($id);

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'This booking cannot be cancelled.');
        }

        $booking->status = 'cancelled';
        $booking->save();

        return back()->with('success', 'Booking cancelled.');
    }

    /**
     * Ensure booking belongs to provider
     */
    private function findProviderBooking($id)
    {
        $providerId = Auth::user()->user_id;

        return Booking::where('id', $id)
            ->whereHas('service', function ($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })
            ->firstOrFail();
    }
}