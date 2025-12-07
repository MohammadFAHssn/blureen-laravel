<?php

namespace App\Models\Food;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MealReservation extends Model
{
    protected $table = 'meal_reservations';

    protected $fillable = [
        'date',
        'meal_id',
        'reserve_type',  // personnel, contractor or guest
        'supervisor_id',  // created_by for now, may change in future - in future use, this means personnel x reserved food for staff, contractor or guest related to personnel y
        'delivery_code',
        'description',  // mandatory only for guest
        'status',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'date' => 'date:Y/m/d',
        'meal_id' => 'integer',
        'supervisor_id' => 'integer',
        'delivery_code' => 'integer',
        'status' => 'boolean',
        'created_by' => 'integer',
        'edited_by' => 'integer',
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

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function details()
    {
        return $this->hasMany(MealReservationDetail::class, 'meal_reservation_id');
    }

    public function scopePersonnel($query)
    {
        return $query->where('reserve_type', 'personnel');
    }

    public function scopeContractor($query)
    {
        return $query->where('reserve_type', 'contractor');
    }

    public function scopeGuest($query)
    {
        return $query->where('reserve_type', 'guest');
    }
}
