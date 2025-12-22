<?php
namespace App\Models;

use App\Models\Base\ApprovalFlow;
use App\Models\Base\UserProfile;
use App\Models\HSE\HealthCertificateUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'personnel_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function approvalFlowsAsRequester()
    {
        return $this->hasMany(ApprovalFlow::class, 'requester_user_id');
    }


    public function healthCertificate()
    {
        return $this->hasMany(HealthCertificateUser::class);
    }
}
