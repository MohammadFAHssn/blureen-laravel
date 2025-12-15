<?php

namespace App\Services\Food\Kitchen;

use App\Models\Food\MealReservation;
use App\Repositories\Food\Reservation\MealReservationDetailRepository;
use App\Repositories\Food\Reservation\MealReservationRepository;
use Illuminate\Validation\ValidationException;

class FoodDeliveryService
{
    /**
     * @var MealReservationRepository
     * @var MealReservationDetailRepository
     */
    protected $mealReservationRepository;

    protected $mealReservationDetailRepository;

    /**
     * FoodDeliveryService constructor
     *
     * @param MealReservationRepository $mealReservationRepository
     * @param MealReservationDetailRepository $mealReservationDetailRepository
     */
    public function __construct(MealReservationRepository $mealReservationRepository, MealReservationDetailRepository $mealReservationDetailRepository)
    {
        $this->mealReservationRepository = $mealReservationRepository;
        $this->mealReservationDetailRepository = $mealReservationDetailRepository;
    }

    /**
     * find a undelivered meal reservation by date and delivery code
     *
     * @param array $data
     * @return MealReservation|null
     * @throws ValidationException
     */
    public function deliverMealReservation($data)
    {
        $mealReservation = $this->mealReservationRepository->deliver($data['reserved_meal_id']);
        if ($data['type'] === 'personnel') {
            $noDeliveryFor = $data['noDeliveryFor'] ?? [];

            $this
                ->mealReservationDetailRepository
                ->markDeliveredExcept($data['reserved_meal_id'], $noDeliveryFor);

            return $mealReservation;
        } else if ($data['type'] === 'contractor') {
            // info('contractor');
        } else if ($data['type'] === 'guest') {
            // info('guest');
        }
    }

    /**
     * find a meal reservation by date and delivery code
     *
     * @param array $data
     * @return MealReservation|null
     * @throws ValidationException
     */
    public function findMealReservation(array $data)
    {
        $mealReservation = $this->mealReservationRepository->findByDateAndDeliveryCode($data);
        return $mealReservation;
    }

    /**
     * Get all meal reservations on a date
     *
     * @param array $data
     * @return Collection|null
     * @throws ValidationException
     */
    public function getAllMealReservationsOnDate(array $data)
    {
        return $this->mealReservationRepository->getAllOnDate($data);
    }
}
