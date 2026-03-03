<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;

     protected $fillable = [
        'id',   
        'name',
        'slug',
        'icon',
        'description',
    ];
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get all services belonging to this category.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}