<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'Job';
    protected $primaryKey = 'job_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'job_id', 'request_id', 'provider_id', 'status', 'accepted_at', 
        'started_at', 'completed_at', 'cancelled_at', 'cancel_reason', 
        'distance_km', 'price_estimated', 'price_final', 'currency', 'eta_minutes'
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'distance_km' => 'decimal:2',
        'price_estimated' => 'decimal:2',
        'price_final' => 'decimal:2',
    ];

    public function dispatchAttempts()
    {
        return $this->hasMany(DispatchAttempt::class, 'job_id', 'job_id');
    }
}