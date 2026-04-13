<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProviderService extends Pivot
{
    protected $table = 'ProviderService';
    public $timestamps = false;
    
    // We set incrementing to false because it uses a composite primary key
    public $incrementing = false;

    protected $fillable = [
        'provider_id', 'service_id', 'price_per_unit', 'min_callout_fee', 
        'qualification_level', 'experience_years', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_per_unit' => 'decimal:2',
        'min_callout_fee' => 'decimal:2',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }

    public function category()
    {
        // HasOneThrough to easily get the category from the provider service
        return $this->hasOneThrough(
            ServiceCategory::class,
            Service::class,
            'service_id', // Foreign key on Service table
            'category_id', // Foreign key on ServiceCategory table
            'service_id', // Local key on ProviderService table
            'category_id' // Local key on Service table
        );
    }
}