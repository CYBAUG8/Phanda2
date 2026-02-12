<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Setting extends Model
{
    use HasFactory;
    protected $primaryKey = 'settings_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'settings_id',
        'user_id',
        'same_gender_provider',
        'repeat_providers',
        'auto_share',
        'two_factor_auth',
        'notifications',
        'notification_preferences',
    ];

    protected $casts = [
        'same_gender_provider' => 'boolean',
        'repeat_providers' => 'boolean',
        'auto_share' => 'boolean',
        'two_factor_auth' => 'boolean',
        'notifications' => 'boolean',
        'notification_preferences' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->settings_id)) {
                $model->settings_id = (string) Str::uuid();
            }
        });
    }
}