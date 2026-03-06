<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $primaryKey = 'service_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'service_id',
        'category_id',
        'provider_id',
        'provider_name',
        'title',
        'description',
        'base_price',
        'min_duration',
        'location',
        'rating',
        'reviews_count',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'rating' => 'decimal:1',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->service_id) {
                $model->service_id = (string) Str::uuid();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_id', 'provider_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'service_id', 'service_id');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'R' . number_format((float) $this->base_price, 2);
    }

    public function getFormattedDurationAttribute(): string
    {
        $minutes = (int) $this->min_duration;
        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        if ($hours > 0 && $remainder > 0) {
            return "{$hours}h {$remainder}m";
        }

        if ($hours > 0) {
            return "{$hours}h";
        }

        return "{$minutes}m";
    }
}
