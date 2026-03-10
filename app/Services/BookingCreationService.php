<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;

class BookingCreationService
{
    public function createFromService(User $user, Service $service, array $attributes): Booking
    {
        return Booking::create([
            'user_id' => $user->user_id,
            'service_id' => $service->service_id,
            'booking_date' => $attributes['booking_date'],
            'start_time' => $attributes['start_time'],
            'status' => Booking::STATUS_PENDING,
            'payment_status' => Booking::PAYMENT_STATUS_UNPAID,
            'payment_due_at' => null,
            'total_price' => $service->base_price,
            'address' => $attributes['address'],
            'notes' => $attributes['notes'] ?? null,
        ]);
    }
}
