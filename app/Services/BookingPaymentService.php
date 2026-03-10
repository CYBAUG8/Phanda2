<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Refund;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingPaymentService
{
    public const PROVIDER = 'mock_gateway';

    public function markPaymentRequired(Booking $booking): void
    {
        $booking->update([
            'payment_status' => Booking::PAYMENT_STATUS_REQUIRED,
            'payment_due_at' => $this->scheduledStartAt($booking),
        ]);
    }

    public function recordSuccessfulPayment(Booking $booking, string $method): Payment
    {
        return DB::transaction(function () use ($booking, $method) {
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'provider' => self::PROVIDER,
                'method' => $method,
                'amount' => (float) $booking->total_price,
                'currency' => 'ZAR',
                'status' => 'paid',
                'reference' => $this->generateReference(),
                'paid_at' => now(),
                'metadata' => [
                    'simulated' => true,
                ],
            ]);

            $booking->update([
                'payment_status' => Booking::PAYMENT_STATUS_PAID,
                'payment_due_at' => null,
            ]);

            return $payment;
        });
    }

    public function recordFailedPayment(Booking $booking, string $method): Payment
    {
        return DB::transaction(function () use ($booking, $method) {
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'provider' => self::PROVIDER,
                'method' => $method,
                'amount' => (float) $booking->total_price,
                'currency' => 'ZAR',
                'status' => 'failed',
                'reference' => $this->generateReference(),
                'failed_at' => now(),
                'metadata' => [
                    'simulated' => true,
                ],
            ]);

            $booking->update([
                'payment_status' => Booking::PAYMENT_STATUS_FAILED,
            ]);

            return $payment;
        });
    }

    public function refundIfEligible(Booking $booking, string $reason = 'Cancelled before start time'): ?Refund
    {
        if (!$this->canAutoRefund($booking)) {
            return null;
        }

        return DB::transaction(function () use ($booking, $reason) {
            /** @var Payment|null $payment */
            $payment = Payment::query()
                ->where('booking_id', $booking->id)
                ->where('status', 'paid')
                ->latest('created_at')
                ->first();

            if ($payment === null) {
                return null;
            }

            $payment->update([
                'status' => 'refunded',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'refunded' => true,
                ]),
            ]);

            $refund = Refund::create([
                'payment_id' => $payment->payment_id,
                'amount' => (float) $booking->total_price,
                'status' => 'refunded',
                'reason' => $reason,
                'refunded_at' => now(),
            ]);

            $booking->update([
                'payment_status' => Booking::PAYMENT_STATUS_REFUNDED,
                'payment_due_at' => null,
            ]);

            return $refund;
        });
    }

    public function canAutoRefund(Booking $booking): bool
    {
        if ($booking->payment_status !== Booking::PAYMENT_STATUS_PAID) {
            return false;
        }

        $scheduledStart = $this->scheduledStartAt($booking);
        if ($scheduledStart === null) {
            return false;
        }

        return CarbonImmutable::now(BookingLifecycleService::BUSINESS_TIMEZONE)->lt($scheduledStart);
    }

    public function scheduledStartAt(Booking $booking): ?CarbonImmutable
    {
        if (!$booking->booking_date || !$booking->start_time) {
            return null;
        }

        $date = $booking->booking_date->format('Y-m-d');
        $time = CarbonImmutable::parse((string) $booking->start_time, BookingLifecycleService::BUSINESS_TIMEZONE)
            ->format('H:i:s');

        $start = CarbonImmutable::createFromFormat(
            'Y-m-d H:i:s',
            "{$date} {$time}",
            BookingLifecycleService::BUSINESS_TIMEZONE
        );

        return $start === false ? null : $start;
    }

    private function generateReference(): string
    {
        return 'MOCK-' . strtoupper(Str::random(10));
    }
}
