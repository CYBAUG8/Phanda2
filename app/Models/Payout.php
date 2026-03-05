<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payout extends Model
{
    protected $table = 'payouts';

    protected $primaryKey = 'payout_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'payout_id',
        'provider_id',
        'amount',
        'currency',
        'status',
        'scheduled_at',
        'paid_at',
        'reference'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            if (!$model->payout_id) {
                $model->payout_id = (string) Str::uuid();
            }

        });
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id', 'user_id');
    }
}
