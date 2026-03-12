<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class BookingLifecycleService
{
    public const BUSINESS_TIMEZONE = 'Africa/Johannesburg';

    public function expireStaleBookings(?Builder $query = null): int
    {
        $query ??= Booking::query();

        $bookings = (clone $query)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with('service')
            ->get();

        $now = $this->businessNow();
        $expiredCount = 0;

        foreach ($bookings as $booking) {
            if ($this->expireIfStale($booking, $now)) {
                $expiredCount++;
            }
        }

        return $expiredCount;
    }

    public function syncBooking(Booking $booking): Booking
    {
        $booking->loadMissing('service');

        $this->expireIfStale($booking, $this->businessNow());

        return $booking->fresh(['service']) ?? $booking;
    }

    public function businessNow(): CarbonImmutable
    {
        return CarbonImmutable::now(self::BUSINESS_TIMEZONE);
    }

    public function scheduledStartAt(Booking $booking): ?CarbonImmutable
    {
        if (!$booking->booking_date || !$booking->start_time) {
            return null;
        }

        $date = $booking->booking_date->format('Y-m-d');
        $time = CarbonImmutable::parse((string) $booking->start_time, self::BUSINESS_TIMEZONE)->format('H:i:s');

        $start = CarbonImmutable::createFromFormat(
            'Y-m-d H:i:s',
            "{$date} {$time}",
            self::BUSINESS_TIMEZONE
        );

        return $start === false ? null : $start;
    }

    public function scheduledEndAt(Booking $booking): ?CarbonImmutable
    {
        $start = $this->scheduledStartAt($booking);

        if ($start === null) {
            return null;
        }

        return $start->addMinutes($this->resolveDurationMinutes($booking));
    }

    private function expireIfStale(Booking $booking, CarbonImmutable $businessNow): bool
    {
        if (in_array($booking->status, ['completed', 'cancelled'], true)) {
            return false;
        }

        $scheduledEndAt = $this->scheduledEndAt($booking);

        if ($scheduledEndAt === null || $businessNow->lt($scheduledEndAt)) {
            return false;
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => Booking::CANCELLATION_REASON_EXPIRED,
            'cancelled_at' => now(),
        ]);

        return true;
    }

    private function resolveDurationMinutes(Booking $booking): int
    {
        return max((int) ($booking->service?->min_duration ?? 60), 30);
    }
}
