<?php

namespace App\Services\Food\Kitchen;

use App\Models\Food\Meal;
use App\Repositories\Food\Kitchen\MealRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class MealService
{
    /**
     * @var mealRepository
     */
    protected $mealRepository;

    /**
     * MealService constructor
     *
     * @param MealRepository $mealRepository
     */
    public function __construct(MealRepository $mealRepository)
    {
        $this->mealRepository = $mealRepository;
    }

    /**
     * create new meal
     *
     * @param array $data
     * @return \App\Models\Food\Meal
     */
    public function createMeal($request)
    {
        // Check for meal existence with same name
        if ($this->mealRepository->mealExist(
            $request
        )) {
            throw ValidationException::withMessages([
                'meal_exist' => ['این وعده غذایی، در پایگاه داده وجود دارد.']
            ]);
        }
        $meal = $this->mealRepository->create($request);
        return $this->formatMealPayload($meal);
    }

    /**
     * Get all meals
     *
     * @return array
     */
    public function getAllMeals()
    {
        $meals = $this->mealRepository->getAll();
        return $this->formatMealsListPayload($meals);
    }

    /**
     * Get all active meals
     *
     * @return array
     */
    public function getAllActiveMeals()
    {
        $meals = $this->mealRepository->getAllActive();
        return $this->formatMealsListPayload($meals);
    }

    /**
     * Update meal
     *
     * @param int $id
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function updateMeal(int $id, array $data)
    {
        $meal = $this->mealRepository->update($id, $data);
        return $this->formatMealPayload($meal);
    }

    /**
     * Delete meal
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->mealRepository->delete($id);
    }

    public function changeStatus(int $id)
    {
        $meal = $this->mealRepository->status($id);
        if ($meal) {
            return $this->formatMealPayload($meal);
        }
        return null;
    }

    /**
     * Format single meal payload
     *
     * @param Meal $meal
     * @return array
     */
    protected function formatMealPayload(Meal $meal): array
    {
        return [
            'id' => $meal->id,
            'name' => $meal->name,
            'status' => $meal->status,
            'createdBy' => $meal->createdBy ? [
                'id' => $meal->createdBy->id,
                'fullName' => $meal->createdBy->first_name . ' ' . $meal->createdBy->last_name,
                'username' => $meal->createdBy->username,
            ] : null,
            'editedBy' => $meal->editedBy ? [
                'id' => $meal->editedBy->id,
                'fullName' => $meal->editedBy->first_name . ' ' . $meal->editedBy->last_name,
                'username' => $meal->editedBy->username,
            ] : null,
            'createdAt' => $meal->created_at,
            'updatedAt' => $meal->updated_at,
        ];
    }

    /**
     * Format meals list payload
     *
     * @param \Illuminate\Database\Eloquent\Collection $meals
     * @return array
     */
    protected function formatMealsListPayload($meals): array
    {
        return [
            'meals' => $meals->map(function ($meal) {
                return $this->formatMealPayload($meal);
            })->toArray(),
            'metadata' => [
                'total' => $meals->count(),
                'retrievedAt' => Carbon::now(),
            ],
        ];
    }
}
