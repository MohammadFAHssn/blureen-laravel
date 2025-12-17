<?php

namespace App\Services\Food\Kitchen;

use App\Models\Food\MealPlan;
use App\Repositories\Food\Kitchen\MealPlanRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\Food\MealReservationDetail;
use App\Repositories\Food\Kitchen\FoodRepository;
use App\Repositories\Food\Reservation\MealReservationRepository;

class MealPlanService
{
    /**
     * @var mealPlanRepository
     * @var mealReservationRepository
     * @var foodRepository
     */
    protected $mealPlanRepository;
    protected $mealReservationRepository;
    protected $foodRepository;

    /**
     * MealService constructor
     *
     * @param MealPlanRepository $mealPlanRepository
     * @param MealReservationRepository $mealReservationRepository
     * @param FoodRepository $foodRepository
     */
    public function __construct(MealPlanRepository $mealPlanRepository, MealReservationRepository $mealReservationRepository, FoodRepository $foodRepository)
    {
        $this->mealPlanRepository = $mealPlanRepository;
        $this->mealReservationRepository = $mealReservationRepository;
        $this->foodRepository = $foodRepository;
    }

    /**
     * create new meal plan
     *
     * @param array $data
     * @return \App\Models\Food\MealPlan
     */
    public function createMealPlan($data)
    {
        return DB::transaction(function () use ($data) {
            // Check for meal plan existence with same date and meal Id
            if ($this->mealPlanRepository->mealPlanExist($data)) {
                throw ValidationException::withMessages([
                    'meal_plan_exist' => ['این وعده غذایی برای این تاریخ، در پایگاه داده وجود دارد.']
                ]);
            }
            $mealPlan = $this->mealPlanRepository->create($data);
            $food = $this->foodRepository->findById($data['food_id']);

            MealReservationDetail::whereHas('reservation', function ($q) use ($data, $mealPlan) {
                $q
                    ->whereDate('date', $data['date'])
                    ->where('status', 0) // not delivered
                    ->where('meal_id', $mealPlan->meal->id);
            })->update([
                'food_id' => $food->id,
                'food_price' => $food->price,
            ]);

            return $this->formatMealPlanPayload($mealPlan);
        });
    }

    /**
     * Get all meal plans for a date
     *
     * @param array $data
     * @return array
     */
    public function getAllMealPlansForDate($request)
    {
        $mealPlans = $this->mealPlanRepository->getAllForDate($request);
        return $this->formatMealsListPayload($mealPlans);
    }

    /**
     * Update meal plan
     *
     * @param int $id
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function updateMealPlan(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $food = $this->foodRepository->findById($data['food_id']);
            $mealPlan = $this->mealPlanRepository->update($id, $data);
            MealReservationDetail::whereHas('reservation', function ($q) use ($data, $mealPlan) {
                $q
                    ->whereDate('date', $data['date'])
                    ->where('status', 0) // not delivered
                    ->where('meal_id', $mealPlan->meal->id);
            })->update([
                'food_id' => $food->id,
                'food_price' => $food->price,
            ]);

            return $this->formatMealPlanPayload($mealPlan);
        });
    }

    /**
     * Format single meal plan payload
     *
     * @param MealPlan $mealPlan
     * @return array
     */
    protected function formatMealPlanPayload(MealPlan $mealPlan): array
    {
        return [
            'id' => $mealPlan->id,
            'date' => $mealPlan->date,
            'meal' => $mealPlan->meal ? [
                'id' => $mealPlan->meal->id,
                'name' => $mealPlan->meal->name,
            ] : null,
            'food' => $mealPlan->food ? [
                'id' => $mealPlan->food->id,
                'name' => $mealPlan->food->name,
                'price' => $mealPlan->food->price,
            ] : null,
            'createdBy' => $mealPlan->createdBy ? [
                'id' => $mealPlan->createdBy->id,
                'fullName' => $mealPlan->createdBy->first_name . ' ' . $mealPlan->createdBy->last_name,
                'username' => $mealPlan->createdBy->username,
            ] : null,
            'editedBy' => $mealPlan->editedBy ? [
                'id' => $mealPlan->editedBy->id,
                'fullName' => $mealPlan->editedBy->first_name . ' ' . $mealPlan->editedBy->last_name,
                'username' => $mealPlan->editedBy->username,
            ] : null,
            'createdAt' => $mealPlan->created_at,
            'updatedAt' => $mealPlan->updated_at,
        ];
    }

    /**
     * Format meal plans list payload
     *
     * @param \Illuminate\Database\Eloquent\Collection $mealPlans
     * @return array
     */
    protected function formatMealsListPayload($mealPlans): array
    {
        return [
            'mealPlans' => $mealPlans->map(function ($mealPlan) {
                return $this->formatMealPlanPayload($mealPlan);
            })->toArray(),
            'metadata' => [
                'total' => $mealPlans->count(),
                'retrievedAt' => Carbon::now(),
            ],
        ];
    }
}
