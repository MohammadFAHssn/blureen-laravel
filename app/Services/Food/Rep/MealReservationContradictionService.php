<?php

namespace App\Services\Food\Rep;

use App\Repositories\Food\Reservation\MealReservationDetailRepository;

class MealReservationContradictionService
{
    /**
     * @var MealReservationDetailRepository
     */
    protected $mealReservationDetailRepository;

    /**
     * MealReservationContradictionService constructor
     *
     */
    public function __construct()
    {
        $this->mealReservationDetailRepository = new MealReservationDetailRepository();
    }

    /**
     * Get delivered personnel meal reservation details for a given meal within a date range.
     *
     * @param  array     $data
     * @return bool
     */
    public function reservationDetailsByDateRangeAndMeal($data)
    {
        $from = $data['date'][0];
        $to = $data['date'][1];
        $mealReservationDetails = $this->mealReservationDetailRepository->deliveredPersonnelReservationDetailsByDateRangeAndMeal($from, $to, $data['meal_id']);
        return $mealReservationDetails;
    }

    /**
     * Get delivered personnel reservation details that still need a checkout lookup.
     *
     * Criteria:
     * - delivery_status = 1
     * - check_out_time is NULL
     * - last_check_at is NULL OR last_check_at is older than 3 days
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\MealReservationDetail>
     */
    public function reservationDetailsNeedingCheckoutCheck()
    {
        return $this->mealReservationDetailRepository->personnelReservationDetailsNeedingCheckoutCheck();
    }
}
