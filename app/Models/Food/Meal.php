<?php

namespace App\Models\Food;

use App\Models\User;
use App\Models\Food\Food;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $table = 'meals';

    protected $fillable = [
        'name',
        'status',
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
