<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;

class UserBookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

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

        return view('users.bookings', compact('bookings', 'stats', 'activeStatus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,service_id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'address' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'search_lat' => 'nullable|numeric|between:-90,90',
            'search_lng' => 'nullable|numeric|between:-180,180',
            'radius_km' => 'nullable|numeric|min:1|max:100',
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
            $coordinates['lat'],
            $coordinates['lng'],
            (float) $provider->last_lat,
            (float) $provider->last_lng
        );

        $userRadiusKm = max(1.0, min((float) ($validated['radius_km'] ?? 25), 100.0));
        if ($distanceKm > $userRadiusKm) {
            return redirect()->back()->withInput()->with('error', 'This provider is outside your selected radius.');
        }

        $providerRadiusKm = (float) ($provider->service_radius_km ?? 0);
        if ($providerRadiusKm > 0 && $distanceKm > $providerRadiusKm) {
            return redirect()->back()->withInput()->with('error', 'You are outside this provider\'s service area.');
        }

        Booking::create([
            'user_id' => $request->user()->user_id,
            'service_id' => $service->service_id,
            'booking_date' => $validated['booking_date'],
            'start_time' => $validated['start_time'],
            'status' => 'pending',
            'total_price' => $service->base_price,
            'address' => $validated['address'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('users.bookings')->with('success', 'Service request sent to provider.');
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->user_id !== $request->user()->user_id) {
            abort(403);
        }

        if (!$booking->can_cancel) {
            return redirect()->route('users.bookings')->with('error', 'This booking cannot be cancelled.');
        }

        $booking->update(['status' => 'cancelled']);

        return redirect()->route('users.bookings')->with('success', 'Booking has been cancelled.');
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

        if ($address === null) {
            return null;
        }

        return [
            'lat' => (float) $address->latitude,
            'lng' => (float) $address->longitude,
        ];
    }

    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(max(0, 1 - $a)));

        return $earthRadiusKm * $c;
    }
}
