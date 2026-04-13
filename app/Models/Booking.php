<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'service_id',
        'booking_date',
        'start_time',
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

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'badge--pending',
            'confirmed' => 'badge--confirmed',
            'in_progress' => 'badge--in-progress',
            'completed' => 'badge--completed',
            'cancelled' => 'badge--cancelled',
            default => 'badge--pending',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst((string) $this->status),
        };
    }

    public function getCanCancelAttribute(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }
}