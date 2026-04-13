<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';

    protected $primaryKey = 'payment_method_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'payment_method_id',
        'user_id',
        'brand',
        'holder_name',
        'last_four',
        'expiry_month',
        'expiry_year',
        'token',
        'is_default',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->payment_method_id) {
                $model->payment_method_id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_method_id', 'payment_method_id');
    }

    public function getMaskedNumberAttribute(): string
    {
        return '**** **** **** ' . $this->last_four;
    }
}
