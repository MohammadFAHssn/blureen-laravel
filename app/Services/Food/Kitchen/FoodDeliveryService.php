<?php

namespace App\Services\Food\Kitchen;

use App\Models\Food\MealReservation;
use App\Repositories\Food\Kitchen\FoodRepository;
use App\Repositories\Food\Reservation\MealReservationDetailRepository;
use App\Repositories\Food\Reservation\MealReservationRepository;
use Illuminate\Validation\ValidationException;

class FoodDeliveryService
{
    /**
     * @var MealReservationRepository
     * @var MealReservationDetailRepository
     * @var FoodRepository
     */
    protected $mealReservationRepository;

    protected $mealReservationDetailRepository;
    protected $foodRepository;

    /**
     * FoodDeliveryService constructor
     *
     * @param MealReservationRepository $mealReservationRepository
     * @param MealReservationDetailRepository $mealReservationDetailRepository
     * @param FoodRepository $foodRepository
     */
    public function __construct(MealReservationRepository $mealReservationRepository, MealReservationDetailRepository $mealReservationDetailRepository, FoodRepository $foodRepository)
    {
        $this->mealReservationRepository = $mealReservationRepository;
        $this->mealReservationDetailRepository = $mealReservationDetailRepository;
        $this->foodRepository = $foodRepository;
    }

    /**
     * deliver a meal reservation
     *
     * @param array $data
     * @return MealReservation|null
     * @throws ValidationException
     */
    public function deliverMealReservation($data)
    {
        $mealReservation = $this->mealReservationRepository->deliver($data['reserved_meal_id']);
        if ($data['type'] === 'personnel') {
            $this->mealReservationDetailRepository->markDeliveredExcept($data['reserved_meal_id'], $data['noDeliveryFor'] ?? []);
        }
        if ($data['type'] === 'guest') {
            $this->mealReservationDetailRepository->markDeliveredExcept($data['reserved_meal_id']);
            return $mealReservation;
        }

        if ($data['type'] === 'contractor') {
            // base (today food)
            $this->mealReservationDetailRepository->update($data['reserved_meal_id'], [
                'quantity' => $data['today_food_count'],
                'delivery_status' => 1,
            ]);
            // second food
            if (isset($data['second_food'])) {
                $secondFood = $this->foodRepository->findById($data['second_food']);
                $this->mealReservationDetailRepository->create([
                    'meal_reservation_id' => $data['reserved_meal_id'],
                    'food_id' => $secondFood->id,
                    'food_price' => $secondFood->price,
                    'delivery_status' => 1,
                    'reserved_for_contractor' => $data['contractor'],
                    'quantity' => $data['second_food_received_count'],
                ]);
            }
            // third food
            if (isset($data['third_food'])) {
                $thirdFood = $this->foodRepository->findById($data['third_food']);
                $this->mealReservationDetailRepository->create([
                    'meal_reservation_id' => $data['reserved_meal_id'],
                    'food_id' => $thirdFood->id,
                    'food_price' => $thirdFood->price,
                    'delivery_status' => 1,
                    'reserved_for_contractor' => $data['contractor'],
                    'quantity' => $data['third_food_received_count'],
                ]);
            }
        }
        return $mealReservation;
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
