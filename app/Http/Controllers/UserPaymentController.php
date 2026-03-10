<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingPaymentService;
use Illuminate\Http\Request;

class UserPaymentController extends Controller
{
    public function showCheckout(Request $request, Booking $booking)
    {
        $this->authorizeBooking($request, $booking);

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return redirect()->route('users.bookings')->with('error', 'Payment can only be completed for confirmed bookings.');
        }

        if ($booking->payment_status === Booking::PAYMENT_STATUS_PAID) {
            return redirect()->route('users.bookings')->with('success', 'This booking is already paid.');
        }

        return view('Users.checkout', [
            'booking' => $booking->load(['service']),
            'methods' => [
                'card' => 'Card',
                'wallet' => 'Wallet',
                'eft' => 'Instant EFT',
            ],
        ]);
    }

    public function initiate(Request $request, Booking $booking, BookingPaymentService $bookingPaymentService)
    {
        $this->authorizeBooking($request, $booking);

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return redirect()->route('users.bookings')->with('error', 'Payment can only be initiated for confirmed bookings.');
        }

        if ($booking->payment_status !== Booking::PAYMENT_STATUS_PAID) {
            $bookingPaymentService->markPaymentRequired($booking);
        }

        return redirect()->route('users.payments.checkout', $booking->id);
    }

    public function simulateSuccess(Request $request, Booking $booking, BookingPaymentService $bookingPaymentService)
    {
        $this->authorizeBooking($request, $booking);

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return redirect()->route('users.bookings')->with('error', 'Payment can only be completed for confirmed bookings.');
        }

        if ($booking->payment_status === Booking::PAYMENT_STATUS_PAID) {
            return redirect()->route('users.bookings')->with('success', 'This booking is already paid.');
        }

        $validated = $request->validate([
            'method' => 'required|in:card,wallet,eft',
        ]);

        $bookingPaymentService->recordSuccessfulPayment($booking, $validated['method']);

        return redirect()->route('users.bookings')->with('success', 'Payment completed successfully.');
    }

    public function simulateFailure(Request $request, Booking $booking, BookingPaymentService $bookingPaymentService)
    {
        $this->authorizeBooking($request, $booking);

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return redirect()->route('users.bookings')->with('error', 'Payment can only be processed for confirmed bookings.');
        }

        $validated = $request->validate([
            'method' => 'required|in:card,wallet,eft',
        ]);

        $bookingPaymentService->recordFailedPayment($booking, $validated['method']);

        return redirect()->route('users.payments.checkout', $booking->id)->with('error', 'Payment simulation failed. Please retry.');
    }

    private function authorizeBooking(Request $request, Booking $booking): void
    {
        if ($booking->user_id !== $request->user()->user_id) {
            abort(403);
        }
    }
}
