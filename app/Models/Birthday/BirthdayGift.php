<?php

namespace App\Models\Birthday;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BirthdayGift extends Model
{
    protected $fillable = [
        'name',
        'code',
        'image',
        'status',
        'amount',
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
