<?php

namespace App\Models\Food;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;

class MealReservationException extends Model
{
    protected $table = 'meal_reservation_exceptions';

    protected $fillable = [
        'user_id',
        'meal_id',
        'status',
        'created_by',
        'edited_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
