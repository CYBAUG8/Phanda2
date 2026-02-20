<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'provider_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'provider_id',
        'business_name',
        'bio',
        'years_experience',
        'kyc_status',
        'is_online',
        'service_radius_km',
        'last_lat',
        'last_lng',
        'rating_avg',
    ];

    
    public function user()
    {
      return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function services()
    {
      return $this->hasMany(Service::class, 'provider_id', 'provider_id');
    }
}
