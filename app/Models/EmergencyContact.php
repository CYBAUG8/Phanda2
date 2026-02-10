<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmergencyContact extends Model
{
    use HasFactory;
    protected $primaryKey = 'emergency_contact_id'; 
    public $incrementing = false;                    
    protected $keyType = 'string';     

    protected $fillable = [
        'emergency_contact_id',
        'user_id',
        'name',
        'phone',
        'relationship',
        'is_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];
       protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->emergency_contact_id) {
                $model->emergency_contact_id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}