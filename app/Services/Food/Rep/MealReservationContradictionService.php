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
     * @param MealReservationDetailRepository $mealReservationDetailRepository
     */
    public function __construct(MealReservationDetailRepository $mealReservationDetailRepository)
    {
        $this->mealReservationDetailRepository = $mealReservationDetailRepository;
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
}
