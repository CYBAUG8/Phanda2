<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'provider_id',
        'address_id',
        'service_id',
        'booking_date',
        'start_time',
        'end_time',
        'status',
        'total_price',
        'notes',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'total_price' => 'decimal:2',
        ];
    }

       public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Format the price in South African Rands.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'R' . number_format($this->total_price, 2);
    }

    /**
     * Get the status badge colour class.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'badge--pending',
            'confirmed'   => 'badge--confirmed',
            'in_progress' => 'badge--in-progress',
            'completed'   => 'badge--completed',
            'cancelled'   => 'badge--cancelled',
            default       => 'badge--pending',
        };
    }

    /**
     * Human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'Pending',
            'confirmed'   => 'Confirmed',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled',
            default       => ucfirst($this->status),
        };
    }

    /**
     * Check if this booking can be cancelled.
     */
    public function getCanCancelAttribute(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }
           protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->booking_id) {
                $model->booking_id = (string) Str::uuid();
            }
        });
    }
}