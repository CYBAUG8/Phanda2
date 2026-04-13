<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Refund extends Model
{
    protected $table = 'refunds';

    protected $primaryKey = 'refund_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'refund_id',
        'payment_id',
        'amount',
        'status',
        'reason',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refunded_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->refund_id) {
                $model->refund_id = (string) Str::uuid();
            }
        });
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }
}
