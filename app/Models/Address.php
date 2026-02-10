<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Address extends Model
{
    protected $primaryKey = 'address_id'; 
    public $incrementing = false;                    
    protected $keyType = 'string';     

    protected $fillable = [
        'address_id',
        'user_id',
        'type',
        'street',
        'city',
        'province',
        'postal_code',
        'country',
        'is_default',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->address_id) {
                $model->address_id = (string) Str::uuid();
            }
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}