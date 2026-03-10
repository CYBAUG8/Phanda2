<?php

namespace App\Console\Commands;

use App\Services\BookingLifecycleService;
use Illuminate\Console\Command;

class ExpireStaleBookings extends Command
{
    protected $signature = 'bookings:expire-stale';

    protected $description = 'Expire unfinished bookings whose scheduled end time has passed.';

    public function handle(BookingLifecycleService $bookingLifecycleService): int
    {
        $expiredCount = $bookingLifecycleService->expireStaleBookings();

        $this->info("Expired {$expiredCount} stale booking(s).");

        return self::SUCCESS;
    }
}