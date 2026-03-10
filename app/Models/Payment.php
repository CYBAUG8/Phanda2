<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $table = 'payments';

    protected $primaryKey = 'payment_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'payment_id',
        'booking_id',
        'user_id',
        'provider',
        'method',
        'amount',
        'currency',
        'status',
        'reference',
        'paid_at',
        'failed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->payment_id) {
                $model->payment_id = (string) Str::uuid();
            }
        });
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'payment_id', 'payment_id');
    }
}
