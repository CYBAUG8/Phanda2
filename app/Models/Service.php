<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
            'price' => 'decimal:2',
            'rating' => 'decimal:1',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the category this service belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all bookings for this service.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Format the price in South African Rands.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'R' . number_format($this->price, 2);
    }

    /**
     * Format duration for display.
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}min";
        } elseif ($hours > 0) {
            return "{$hours}h";
        }
        return "{$minutes}min";
    }
   public function providerProfile()
    {
      return $this->belongsTo(ProviderProfile::class, 'provider_id', 'provider_id');
    } 
             protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->service_id) {
                $model->service_id = (string) Str::uuid();
            }
        });
    }
}