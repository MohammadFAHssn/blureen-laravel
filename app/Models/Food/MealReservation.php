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
        'reserve_type',  // personnel, contractor, guest or repairman
        'supervisor_id',  // created_by for now, may change in future - in future use, this means personnel x reserved food for staff, contractor or guest related to personnel y (personnel y Id)
        'delivery_code',
        'description',  // mandatory only for guest and repairman
        'serve_place',  // mandatory only for guest - serve_in_kitchen or deliver
        'attendance_hour',  // mandatory only for guest when serve_place is serve_in_kitchen
        'status',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'date' => 'date:Y/m/d',
        'meal_id' => 'integer',
        'reserve_type' => 'string',
        'supervisor_id' => 'integer',
        'delivery_code' => 'integer',
        'description' => 'string',
        'serve_place' => 'string',
        'attendance_hour' => 'string',
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
        return $this->hasMany(MealReservationDetail::class, 'meal_reservation_id')->with('food', 'contractor', 'personnel');
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

    public function scopeRepairman($query)
    {
        return $query->where('reserve_type', 'repairman');
    }
}
