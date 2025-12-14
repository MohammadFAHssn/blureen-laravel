<?php

namespace App\Services\Food\Kitchen;

use App\Models\Food\MealReservation;
use App\Repositories\Food\Reservation\MealReservationRepository;
use Illuminate\Validation\ValidationException;

class FoodDeliveryService
{
    /**
     * @var MealReservationRepository
     */
    protected $mealReservationRepository;

    /**
     * FoodDeliveryService constructor
     *
     * @param MealReservationRepository $mealReservationRepository
     */
    public function __construct(MealReservationRepository $mealReservationRepository)
    {
        $this->mealReservationRepository = $mealReservationRepository;
    }

    /**
     * find a undelivered meal reservation by date and delivery code
     *
     * @param array $data
     * @return MealReservation|null
     * @throws ValidationException
     */
    public function findUndeliveredMealReservation(array $data)
    {
        $mealReservation = $this->mealReservationRepository->findByUndeliveredDateAndDeliveryCode($data);
        return $mealReservation;
    }

    /**
     * Get all undelivered meal reservations on a date
     *
     * @param array $data
     * @return Collection|null
     * @throws ValidationException
     */
    public function getAllUndeliveredMealReservationsOnDate(array $data)
    {
        return $this->mealReservationRepository->getAllUndeliveredOnDate($data);
    }
}
