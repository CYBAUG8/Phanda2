<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LoginHistory extends Model
{
    use HasFactory;
    
    protected $table = 'login_histories';

    protected $primaryKey = 'login_history_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'login_history_id',
        'user_id',
        'login_at',
        'ip_address',
        'user_agent',
        'device',
        'location',
        'status',
    ];

    protected $casts = [
        'login_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('login_at', '>=', now()->subDays($days));
    }
         protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->login_history_id) {
                $model->login_history_id = (string) Str::uuid();
            }
        });
    }
}