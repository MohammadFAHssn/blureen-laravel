<?php

namespace App\Models\Contractor;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    protected $table = 'contractors';

    protected $fillable = [
        'first_name',
        'last_name',
        'national_code',
        'mobile_number',
        'active',
        'created_by',
        'edited_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
