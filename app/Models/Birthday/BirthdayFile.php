<?php

namespace App\Models\Birthday;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BirthdayFile extends Model
{
    protected $fillable = [
        'file_name',
        'month',
        'year',
        'status',
        'uploaded_by',
        'edited_by',
    ];

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
