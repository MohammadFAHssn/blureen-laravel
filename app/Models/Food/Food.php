<?php

namespace App\Models\Food;

use App\Models\User;
use App\Models\Food\Meal;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';

    protected $fillable = [
        'name',
        'status',
        'price',
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
