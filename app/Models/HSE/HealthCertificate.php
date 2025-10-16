<?php

namespace App\Models\HSE;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class HealthCertificate extends Model
{
    protected $fillable = [
        'file_name',
        'month',
        'year',
        'status',
        'uploaded_by',
        'edited_by',
    ];

    public function users()
    {
        return $this->hasMany(HealthCertificateUser::class, 'health_certificate_id');
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
