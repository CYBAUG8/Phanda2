<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RecoveryContact extends Model
{
    protected $primaryKey = 'recovery_contact_id';
    
    protected $fillable = [
        'recovery_contact_id',
        'user_id',
        'name',
        'phone',
        'email',
        'relationship',
    ];
 
       protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->recovery_contact_id) {
                $model->recovery_contact_id = (string) Str::uuid();
            }
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}