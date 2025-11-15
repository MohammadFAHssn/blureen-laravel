<?php

namespace App\Models\HSE;

use App\Models\User;
use App\Models\HSE\HealthCertificate;
use Illuminate\Database\Eloquent\Model;

class HealthCertificateUser extends Model
{
    protected $fillable = [
        'health_certificate_id',
        'user_id',
        'image',
        'status',
        'uploaded_by',
        'edited_by',
    ];

    protected $table = 'health_certificates_users';

    public function healthCertificate()
    {
        return $this->belongsTo(HealthCertificate::class, 'health_certificate_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
