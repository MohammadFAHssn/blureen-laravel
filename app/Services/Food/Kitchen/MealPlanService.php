<?php

namespace App\Services\Food\Kitchen;

use App\Models\Food\MealPlan;
use App\Repositories\Food\Kitchen\MealPlanRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class MealPlanService
{
    /**
     * @var mealPlanRepository
     */
    protected $mealPlanRepository;

    /**
     * MealService constructor
     *
     * @param MealPlanRepository $mealPlanRepository
     */
    public function __construct(MealPlanRepository $mealPlanRepository)
    {
        $this->mealPlanRepository = $mealPlanRepository;
    }

    /**
     * create new meal plan
     *
     * @param array $data
     * @return \App\Models\Food\MealPlan
     */
    public function createMealPlan($request)
    {
        // Check for meal plan existence with same date and meal Id
        if ($this->mealPlanRepository->mealPlanExist(
            $request
        )) {
            throw ValidationException::withMessages([
                'meal_plan_exist' => ['این وعده غذایی برای این تاریخ، در پایگاه داده وجود دارد.']
            ]);
        }
        $mealPlan = $this->mealPlanRepository->create($request);
        return $this->formatMealPlanPayload($mealPlan);
    }

    /**
     * Get all meal plans
     *
     * @return array
     */
    public function getAllMealPlans()
    {
        $mealPlans = $this->mealPlanRepository->getAll();
        return $this->formatMealsListPayload($mealPlans);
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
        $mealPlan = $this->mealPlanRepository->update($id, $data);
        return $this->formatMealPlanPayload($mealPlan);
    }

    /**
     * Delete meal plan
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->mealPlanRepository->delete($id);
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
