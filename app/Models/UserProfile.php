<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';


    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'password',
        'gender',
        'role',
        'member_id',
        'account_status',
        'email_verified_at',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'data_sharing_enabled' => 'boolean',
        'notifications_enabled' => 'boolean',
        'same_gender_only' => 'boolean',
        'auto_approve_providers' => 'boolean',
        'auto_share_enabled' => 'boolean',
    ];

    // Relationships
    public function settings()
    {
        return $this->hasOne(Setting::class,'user_id');
    }
    
    public function addresses()
    {
        return $this->hasMany(Address::class, 'user_id');
    }

    public function locations()
    {
        return $this->hasMany(Location::class,'user_id');
    }

    public function emergencyContact()
    {
        return $this->hasOne(EmergencyContact::class,'user_id');
    }

     public function recoveryContact()
    {
        return $this->hasOne(RecoveryContact::class, 'user_id');
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class,'user_id');
    }

    public function otps()
    {
        return $this->hasMany(Otp::class,'user_id');
    }

    // Helper methods
   public function getFullNameAttribute($value)
    {
  
         return $value;
    }

    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    public function hasVerifiedPhone()
    {
        return !is_null($this->phone_verified_at);
    }

    public function getNotificationPreferencesAttribute()
    {
        $defaultPreferences = [
            'Promotional Offers' => false,
            'Membership Updates' => false,
            'Reminders' => false,
            'Feedback Requests' => false,
            'Service Provider Updates' => false,
            'System Alerts' => false,
        ];

        if ($this->settings && $this->settings->notification_preferences) {
            return array_merge($defaultPreferences, $this->settings->notification_preferences);
        }

        return $defaultPreferences;
    }
}