<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;

class BookingCreationService
{
    public function createFromService(User $user, Service $service, array $attributes): Booking
    {
        $address = Address::query()
            ->where('user_id', $user->user_id)
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->firstOrFail();

        $endTime = Carbon::createFromFormat('H:i', $attributes['start_time'])
            ->addMinutes(max((int) ($service->duration_minutes ?? $service->min_duration ?? 60), 30))
            ->format('H:i');

        return Booking::create([
            'user_id'        => $user->user_id,
            'service_id'     => $service->service_id,
            'provider_id'    => $service->providerProfile->provider_id,
            'address_id'     => $address->address_id,
            'booking_date'   => $attributes['booking_date'],
            'start_time'     => $attributes['start_time'],
            'end_time'       => $endTime,
            'status'         => Booking::STATUS_PENDING,
            'payment_status' => Booking::PAYMENT_STATUS_UNPAID,
            'payment_due_at' => null,
            'total_price'    => $service->base_price,
            'address'        => $attributes['address'],
            'notes'          => $attributes['notes'] ?? null,
        ]);
    }
}