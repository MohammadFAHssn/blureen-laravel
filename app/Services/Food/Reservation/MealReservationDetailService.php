<?php

namespace App\Services\Food\Reservation;

use App\Repositories\Food\Reservation\MealReservationDetailRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class MealReservationDetailService
{
    /**
     * @var mealReservationDetailRepository
     */
    protected $mealReservationDetailRepository;

    /**
     * MealReservationDetailService constructor
     *
     * @param MealReservationDetailRepository $mealReservationDetailRepository
     */
    public function __construct(MealReservationDetailRepository $mealReservationDetailRepository)
    {
        $this->mealReservationDetailRepository = $mealReservationDetailRepository;
    }

    /**
     * Delete meal reservation detail
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->mealReservationDetailRepository->delete($id);
    }
}
