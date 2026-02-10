<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Location extends Model
{
    use HasFactory;

    protected $primaryKey = 'location_id'; 
    public $incrementing = false;                    
    protected $keyType = 'string';  

    protected $fillable = [
        'location_id',
        'user_id',
        'name',
        'address',
        'type',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeHome($query)
    {
        return $query->where('type', 'home');
    }

    public function scopeWork($query)
    {
        return $query->where('type', 'work');
    }
      protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->location_id) {
                $model->location_id = (string) Str::uuid();
            }
        });
    }
}