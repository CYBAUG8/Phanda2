<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Review extends Model
{
    use HasFactory;

    
    protected $table = 'service_reviews';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'service_id',
        'provider_id',
        'user_id',
        'rating',
        'comment',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // reviewer
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // person being reviewed
    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
