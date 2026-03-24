<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingLifecycleService;
use App\Services\BookingPaymentService;
use Carbon\Carbon;
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

        $status = trim((string) $request->query('status', 'pending'));
        $search = trim((string) $request->query('q', ''));
        $payment = trim((string) $request->query('payment', 'all'));
        $scheduledFor = trim((string) $request->query('scheduled_for', 'all'));
        $sort = trim((string) $request->query('sort', 'newest'));
        $today = Carbon::now('Africa/Johannesburg')->toDateString();

        $providerBookings = $this->providerBookingQuery($providerProfile->provider_id);

        $statusCounts = [
            'all' => (clone $providerBookings)->count(),
            Booking::STATUS_PENDING => (clone $providerBookings)->where('status', Booking::STATUS_PENDING)->count(),
            Booking::STATUS_CONFIRMED => (clone $providerBookings)->where('status', Booking::STATUS_CONFIRMED)->count(),
            Booking::STATUS_IN_PROGRESS => (clone $providerBookings)->where('status', Booking::STATUS_IN_PROGRESS)->count(),
            Booking::STATUS_COMPLETED => (clone $providerBookings)->where('status', Booking::STATUS_COMPLETED)->count(),
            Booking::STATUS_CANCELLED => (clone $providerBookings)->where('status', Booking::STATUS_CANCELLED)->count(),
        ];

        $bookingMetrics = [
            'pending' => (clone $providerBookings)->where('status', Booking::STATUS_PENDING)->count(),
            'confirmed_upcoming' => (clone $providerBookings)
                ->where('status', Booking::STATUS_CONFIRMED)
                ->whereDate('booking_date', '>=', $today)
                ->count(),
            'in_progress' => (clone $providerBookings)->where('status', Booking::STATUS_IN_PROGRESS)->count(),
            'awaiting_payment' => (clone $providerBookings)
                ->where('status', Booking::STATUS_CONFIRMED)
                ->whereIn('payment_status', [
                    Booking::PAYMENT_STATUS_REQUIRED,
                    Booking::PAYMENT_STATUS_UNPAID,
                    Booking::PAYMENT_STATUS_FAILED,
                ])
                ->count(),
        ];

        $bookings = $this->providerBookingQuery($providerProfile->provider_id)
            ->with([
                'user',
                'service' => fn ($query) => $query->withTrashed(),
            ]);

        if (in_array($status, [
            Booking::STATUS_PENDING,
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_IN_PROGRESS,
            Booking::STATUS_COMPLETED,
            Booking::STATUS_CANCELLED,
        ], true)) {
            $bookings->where('status', $status);
        }

        if ($search !== '') {
            $bookings->where(function ($query) use ($search) {
                $query
                    ->where('id', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('notes', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('full_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('service', function ($serviceQuery) use ($search) {
                        $serviceQuery->withTrashed()->where('title', 'like', '%' . $search . '%');
                    });
            });
        }

        $paymentMap = [
            'required' => Booking::PAYMENT_STATUS_REQUIRED,
            'paid' => Booking::PAYMENT_STATUS_PAID,
            'failed' => Booking::PAYMENT_STATUS_FAILED,
            'refunded' => Booking::PAYMENT_STATUS_REFUNDED,
            'unpaid' => Booking::PAYMENT_STATUS_UNPAID,
        ];

        if (array_key_exists($payment, $paymentMap)) {
            $bookings->where('payment_status', $paymentMap[$payment]);
        }

        if ($scheduledFor === 'today') {
            $bookings->whereDate('booking_date', $today);
        }

        if ($scheduledFor === 'upcoming') {
            $bookings->whereDate('booking_date', '>=', $today);
        }

        if ($scheduledFor === 'past') {
            $bookings->whereDate('booking_date', '<', $today);
        }

        match ($sort) {
            'oldest' => $bookings->orderBy('created_at'),
            'scheduled_asc' => $bookings->orderBy('booking_date')->orderBy('start_time'),
            'scheduled_desc' => $bookings->orderByDesc('booking_date')->orderByDesc('start_time'),
            'amount_high' => $bookings->orderByDesc('total_price'),
            'amount_low' => $bookings->orderBy('total_price'),
            default => $bookings->orderByDesc('created_at'),
        };

        $bookings = $bookings
            ->paginate(10)
            ->withQueryString();

        $bookingFilters = [
            'status' => in_array($status, [
                'all',
                Booking::STATUS_PENDING,
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_IN_PROGRESS,
                Booking::STATUS_COMPLETED,
                Booking::STATUS_CANCELLED,
            ], true) ? $status : 'pending',
            'q' => $search,
            'payment' => in_array($payment, ['all', 'required', 'paid', 'failed', 'refunded', 'unpaid'], true)
                ? $payment
                : 'all',
            'scheduled_for' => in_array($scheduledFor, ['all', 'today', 'upcoming', 'past'], true)
                ? $scheduledFor
                : 'all',
            'sort' => in_array($sort, ['newest', 'oldest', 'scheduled_asc', 'scheduled_desc', 'amount_high', 'amount_low'], true)
                ? $sort
                : 'newest',
        ];

        return view('Providers.bookings', compact(
            'bookings',
            'statusCounts',
            'bookingMetrics',
            'bookingFilters'
        ));
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
                $query->withTrashed()->where('provider_id', $providerProfile->provider_id);
            })
            ->with([
                'user',
                'service' => fn ($query) => $query->withTrashed(),
            ])
            ->firstOrFail();
    }

    private function providerBookingQuery(string $providerId)
    {
        return Booking::query()->whereHas('service', function ($query) use ($providerId) {
            $query->withTrashed()->where('provider_id', $providerId);
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
