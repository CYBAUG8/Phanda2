<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'password',
        'phone',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id', 'user_id');
    }

    public function providerProfile(): HasOne
    {
        return $this->hasOne(ProviderProfile::class, 'user_id', 'user_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'user_id', 'user_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'user_id', 'user_id');
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class, 'user_id', 'user_id');
    }

    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class, 'user_id', 'user_id');
    }

    public function emergencyContact(): HasOne
    {
        return $this->hasOne(EmergencyContact::class, 'user_id', 'user_id');
    }

    public function recoveryContact(): HasOne
    {
        return $this->hasOne(RecoveryContact::class, 'user_id', 'user_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_id', 'user_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->user_id) {
                $model->user_id = (string) Str::uuid();
            }
        });
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'from_user_id', 'user_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'to_user_id', 'user_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class, 'provider_id', 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id', 'user_id');
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'user_id', 'user_id');
    }
}
