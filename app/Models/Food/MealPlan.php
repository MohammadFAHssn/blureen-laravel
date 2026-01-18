<?php

namespace App\Models\Food;

use App\Models\Food\Food;
use App\Models\Food\Meal;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MealPlan extends Model
{
    protected $table = 'meal_plans';

    protected $fillable = [
        'date',
        'meal_id',
        'food_id',
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

    public function meal()
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }

    public function food()
    {
        return $this->belongsTo(Food::class, 'food_id');
    }
}
