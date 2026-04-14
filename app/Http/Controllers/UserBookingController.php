<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Booking;
use App\Models\Service;
use App\Services\BookingCreationService;
use App\Services\BookingLifecycleService;
use App\Services\BookingPaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserBookingController extends Controller
{
    public function index(Request $request, BookingLifecycleService $bookingLifecycleService)
    {
        $user = $request->user();

        $bookingLifecycleService->expireStaleBookings(
            Booking::query()->where('user_id', $user->user_id)
        );

        $query = Booking::with(['service.category'])
            ->where('user_id', $user->user_id)
            ->orderByDesc('booking_date')
            ->orderByDesc('start_time');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $bookings = $query->get();

        $allBookings = Booking::where('user_id', $user->user_id);
        $stats = [
            'total' => (clone $allBookings)->count(),
            'upcoming' => (clone $allBookings)->whereIn('status', ['pending', 'confirmed'])->count(),
            'completed' => (clone $allBookings)->where('status', 'completed')->count(),
        ];

        $activeStatus = $request->input('status', 'all');

        return view('Users.bookings', compact('bookings', 'stats', 'activeStatus'));
    }

    public function store(Request $request, BookingCreationService $bookingCreationService)
    {
        $validated = $request->validate([
            'service_id' => ['required', Rule::exists('services', 'service_id')->whereNull('deleted_at')],
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time'   => 'required|date_format:H:i',
            'address'      => 'required|string|max:255',
            'notes'        => 'nullable|string|max:1000',
            'search_lat'   => 'nullable|numeric|between:-90,90',
            'search_lng'   => 'nullable|numeric|between:-180,180',
            'radius_km'    => 'nullable|numeric|min:1|max:100',
        ]);

        $service = Service::query()
            ->with('providerProfile')
            ->where('service_id', $validated['service_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $coordinates = $this->resolveUserCoordinates($request, $validated);

        if ($coordinates === null) {
            return redirect()->back()->withInput()->with('error', 'Set your current location before sending a service request.');
        }

        $provider = $service->providerProfile;
        if ($provider === null || $provider->last_lat === null || $provider->last_lng === null) {
            return redirect()->back()->withInput()->with('error', 'Provider location is unavailable. Please choose another service.');
        }

        $distanceKm = $this->distanceKm(
            $coordinates['lat'], $coordinates['lng'],
            (float) $provider->last_lat, (float) $provider->last_lng
        );

        $userRadiusKm = max(1.0, min((float) ($validated['radius_km'] ?? 25), 100.0));
        if ($distanceKm > $userRadiusKm) {
            return redirect()->back()->withInput()->with('error', 'This provider is outside your selected radius.');
        }

        $providerRadiusKm = (float) ($provider->service_radius_km ?? 0);
        if ($providerRadiusKm > 0 && $distanceKm > $providerRadiusKm) {
            return redirect()->back()->withInput()->with('error', 'You are outside this provider\'s service area.');
        }

        $bookingCreationService->createFromService($request->user(), $service, $validated);

        return redirect()->route('users.bookings')->with('success', 'Service request sent to provider.');
    }

    public function cancel(
        Request $request,
        Booking $booking,
        BookingLifecycleService $bookingLifecycleService,
        BookingPaymentService $bookingPaymentService
    ) {
        if ($booking->user_id !== $request->user()->user_id) {
            abort(403);
        }

        $booking = $bookingLifecycleService->syncBooking($booking);

        if (!$booking->can_cancel) {
            return redirect()->route('users.bookings')->with('error', 'This booking cannot be cancelled.');
        }

        $refund = $bookingPaymentService->refundIfEligible($booking);

        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'cancellation_reason' => Booking::CANCELLATION_REASON_USER,
            'cancelled_at' => now(),
        ]);

        if ($refund !== null) {
            return redirect()->route('users.bookings')->with('success', 'Booking cancelled and full refund processed.');
        }

        return redirect()->route('users.bookings')->with('success', 'Booking has been cancelled.');
    }

    private function maybeShareWithEmergencyContact($user, ServiceRequest $booking): void
    {
        $settings = $user->settings;

        if (!$settings?->auto_share) {
            return;
        }

        $emergencyContact = EmergencyContact::where('user_id', $user->user_id)->first();

        if (!$emergencyContact || !$emergencyContact->phone) {
            return;
        }

        $booking->load(['service', 'user']);

        $date     = \Carbon\Carbon::parse($booking->booking_date)->format('D, d M Y');
        $time     = \Carbon\Carbon::parse($booking->start_time)->format('H:i');
        $price    = 'R' . number_format((float) $booking->total_price, 2);
        $userName = $booking->user->full_name;
        $service  = $booking->service->title ?? 'a service';
        $provider = $booking->service->provider_name ?? 'a provider';
        $address  = $booking->address;

        $message = "🔔 Phanda Safety Alert\n"
            . "Hi {$emergencyContact->name}, {$userName} has booked a service.\n\n"
            . "📋 Service: {$service}\n"
            . "👤 Provider: {$provider}\n"
            . "📅 Date: {$date}\n"
            . "⏰ Time: {$time}\n"
            . "📍 Address: {$address}\n"
            . "💰 Price: {$price}\n\n"
            . "If you have concerns about their safety, please contact them directly.";

        try {
            $twilio = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $twilio->messages->create(
                $emergencyContact->phone,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $message,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to send emergency contact SMS', [
                'user_id' => $user->user_id,
                'contact' => $emergencyContact->phone,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    private function resolveUserCoordinates(Request $request, array $validated): ?array
    {
        if (isset($validated['search_lat'], $validated['search_lng'])) {
            return [
                'lat' => (float) $validated['search_lat'],
                'lng' => (float) $validated['search_lng'],
            ];
        }

        $address = Address::query()
            ->where('user_id', $request->user()->user_id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->first();

        return $address === null ? null : [
            'lat' => (float) $address->latitude,
            'lng' => (float) $address->longitude,
        ];
    }

    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(max(0, 1 - $a)));

        return $earthRadiusKm * $c;
    }
}


