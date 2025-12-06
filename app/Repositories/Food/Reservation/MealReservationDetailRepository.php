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
}
