<?php

namespace App\Repositories\Food\Reservation;

use Illuminate\Support\Facades\Auth;
use App\Models\Food\MealReservationDetail;

class MealReservationDetailRepository
{
    /**
     * create new meal reservation detail
     *
     * @param array $data
     * @return \App\Models\Food\MealReservationDetail
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::id();
        return MealReservationDetail::create($data);
    }

    /**
     * Get meal reservation detail by reservation Id and user Id
     *
     * @param int $id
     * @return collection
     */
    public function findByReservationIdUserId(int $reservationId)
    {
        $userId = Auth::id();
        return MealReservationDetail::where('meal_reservation_id', $reservationId)->where('reserved_for_personnel', $userId)->with('reservation', 'createdBy', 'editedBy')->get();
    }
}
