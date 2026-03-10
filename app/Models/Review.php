<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Review extends Model
{
    use HasFactory;

    protected $table = 'service_reviews';
    protected $primaryKey = 'review_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'review_id',
        'booking_id',
        'service_id',
        'to_user_id',
        'from_user_id',
        'rating',
        'comment',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->review_id) {
                $model->review_id = (string) Str::uuid();
            }
        });
    }

    // reviewer
    public function customer()
    {
        return $this->belongsTo(User::class, 'from_user_id', 'user_id');
    }

    // person being reviewed
    public function provider()
    {
        return $this->belongsTo(User::class, 'to_user_id', 'user_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }
}
