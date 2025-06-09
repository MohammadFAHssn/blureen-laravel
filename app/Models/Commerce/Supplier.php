<?php

namespace App\Models\Commerce;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Supplier extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'otp_code',
        'otp_expires_at',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
