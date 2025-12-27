<?php

namespace App\Models\Food;

use App\Models\Contractor\Contractor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MealReservationDetail extends Model
{
    protected $table = 'meal_reservations_details';

    protected $fillable = [
        'meal_reservation_id',
        'reserved_for_personnel', // personnel id (nullable)
        'reserved_for_contractor',// contractor id (nullable)
        'food_id',
        'food_price',
        'quantity', // 1 for personnel, >=1 guest and >=1 record for same contractor but each record have different quantities
        'delivery_status', // 0 for not_delivered, 1 for delivered
        'check_out_time',
        'last_check_at',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'meal_reservation_id'       => 'integer',
        'reserved_for_personnel'    => 'integer',
        'reserved_for_contractor'   => 'integer',
        'food_id'                   => 'integer',
        'food_price'                => 'integer',
        'quantity'                  => 'integer',
        'delivery_status'           => 'boolean',
        'check_out_time'            => 'string',
        'last_check_at'             => 'date',
        'created_by'                => 'integer',
        'edited_by'                 => 'integer',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function food()
    {
        return $this->belongsTo(Food::class, 'food_id');
    }

    public function reservation()
    {
        return $this->belongsTo(MealReservation::class, 'meal_reservation_id')->with('meal');
    }

    public function personnel()
    {
        return $this->belongsTo(User::class, 'reserved_for_personnel');
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'reserved_for_contractor');
    }
}
