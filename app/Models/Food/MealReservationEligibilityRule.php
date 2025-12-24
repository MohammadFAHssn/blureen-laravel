<?php

namespace App\Models\Food;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MealReservationEligibilityRule extends Model
{
    protected $table = 'meal_reservation_eligibility_rules';

    protected $fillable = [
        'meal_id',
        'time',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'meal_id'                   => 'integer',
        'time'                      => 'string',
        'created_by'                => 'integer',
        'edited_by'                 => 'integer',
    ];

    public function getTimeAttribute($value)
    {
        return $value ? substr($value, 0, 5) : null;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }
}
