<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;

class UserBookingController extends Controller
{
    /**
     * Display the user's bookings with optional status filter.
     */
    public function index(Request $request)
    {
        // Hardcoded user_id=1 for now (no auth system yet)
        $userId = 1;

        $query = Booking::with('service.category')
            ->where('user_id', $userId)
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc');

        // Filter by status tab
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $bookings = $query->get();

        // Calculate stats
        $allBookings = Booking::where('user_id', $userId);
        $stats = [
            'total'     => (clone $allBookings)->count(),
            'upcoming'  => (clone $allBookings)->whereIn('status', ['pending', 'confirmed'])->count(),
            'completed' => (clone $allBookings)->where('status', 'completed')->count(),
        ];

        $activeStatus = $request->input('status', 'all');

        return view('users.bookings', compact('bookings', 'stats', 'activeStatus'));
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id'   => 'required|exists:services,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time'   => 'required|date_format:H:i',
            'address'      => 'required|string|max:255',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $service = Service::findOrFail($validated['service_id']);

        Booking::create([
            'user_id'      => 1, // Hardcoded for now
            'service_id'   => $service->id,
            'booking_date' => $validated['booking_date'],
            'start_time'   => $validated['start_time'],
            'status'       => 'pending',
            'total_price'  => $service->price,
            'address'      => $validated['address'],
            'notes'        => $validated['notes'] ?? null,
        ]);

        return redirect('/users/bookings')->with('success', 'Booking created successfully! Your service provider will confirm shortly.');
    }

    /**
     * Cancel a booking (only pending or confirmed).
     */
    public function cancel(Booking $booking)
    {
        // Ensure this belongs to the current user (hardcoded user_id=1)
        if ($booking->user_id !== 1) {
            abort(403);
        }

        if (!$booking->can_cancel) {
            return redirect('/users/bookings')->with('error', 'This booking cannot be cancelled.');
        }

        $booking->update(['status' => 'cancelled']);

        return redirect('/users/bookings')->with('success', 'Booking has been cancelled.');
    }
}