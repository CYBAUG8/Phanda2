<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_REQUIRED = 'payment_required';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';
    public const PAYMENT_STATUS_FAILED = 'failed';

    public const CANCELLATION_REASON_EXPIRED = 'expired';
    public const CANCELLATION_REASON_USER = 'user_cancelled';
    public const CANCELLATION_REASON_PROVIDER = 'provider_cancelled';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'service_id',
        'booking_date',
        'start_time',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'total_price',
        'notes',
        'address',
        'payment_status',
        'payment_due_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'cancelled_at' => 'datetime',
            'total_price' => 'decimal:2',
            'payment_due_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function getBookingCodeAttribute(): string
    {
        return 'BKG-' . strtoupper(substr((string) $this->id, 0, 8));
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'R' . number_format((float) $this->total_price, 2);
    }

    public function getDisplayStatusAttribute(): string
    {
        return $this->isExpired() ? 'expired' : (string) $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->isExpired()) {
            return 'badge--expired';
        }

        return match ($this->status) {
            self::STATUS_PENDING => 'badge--pending',
            self::STATUS_CONFIRMED => 'badge--confirmed',
            self::STATUS_IN_PROGRESS => 'badge--in-progress',
            self::STATUS_COMPLETED => 'badge--completed',
            self::STATUS_CANCELLED => 'badge--cancelled',
            default => 'badge--pending',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isExpired()) {
            return 'Expired';
        }

        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst((string) $this->status),
        };
    }

    public function getCanCancelAttribute(): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_STATUS_REQUIRED => 'Awaiting Payment',
            self::PAYMENT_STATUS_PAID => 'Paid',
            self::PAYMENT_STATUS_REFUNDED => 'Refunded',
            self::PAYMENT_STATUS_FAILED => 'Payment Failed',
            default => 'Unpaid',
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_STATUS_REQUIRED => 'payment-badge--required',
            self::PAYMENT_STATUS_PAID => 'payment-badge--paid',
            self::PAYMENT_STATUS_REFUNDED => 'payment-badge--refunded',
            self::PAYMENT_STATUS_FAILED => 'payment-badge--failed',
            default => 'payment-badge--unpaid',
        };
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_CANCELLED
            && $this->cancellation_reason === self::CANCELLATION_REASON_EXPIRED;
    }

    public function isManuallyCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED && !$this->isExpired();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'booking_id', 'id');
    }
}
