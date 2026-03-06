<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispatchAttempt extends Model
{
    protected $table = 'DispatchAttempt';
    protected $primaryKey = 'attempt_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'attempt_id', 'job_id', 'provider_id', 'rank_order', 'score', 
        'distance_km', 'sent_at', 'response', 'responded_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'distance_km' => 'decimal:2',
        'score' => 'decimal:2',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id', 'job_id');
    }
}