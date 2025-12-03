<?php

namespace App\Repositories\Food\Kitchen;

use App\Models\Food\MealPlan;
use Illuminate\Support\Facades\Auth;

class MealPlanRepository
{
    /**
     * create new meal plan
     *
     * @param array $data
     * @return \App\Models\Food\MealPlan
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::id();
        return MealPlan::create($data);
    }

    /**
     * Get all meal plans
     *
     * @return array
     */
    public function getAll()
    {
        return MealPlan::with('createdBy', 'editedBy')->get();
    }

    /**
     * Get all meal plans for a date
     *
     * @param array $data
     * @return array
     */
    public function getAllForDate($data)
    {
        return MealPlan::where('date', $data['date'])->with('createdBy', 'editedBy')->get();
    }

    /**
     * Update meal plan
     *
     * @param int $id
     * @param array $data
     * @return MealPlan
     */
    public function update(int $id, array $data)
    {
        $mealPlan = $this->findById($id);
        $data['edited_by'] = Auth::id();
        $mealPlan->update($data);
        return $mealPlan;
    }

    /**
     * Delete meal plan
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        $mealPlan = $this->findById($id);
        return $mealPlan->delete();
    }

    /**
     * Get a by ID
     *
     * @param int $id
     * @return MealPlan
     * @throws ModelNotFoundException
     */
    public function findById(int $id): MealPlan
    {
        return MealPlan::findOrFail($id);
    }

    /**
     * Check if there's a meal plan with the same date and meal Id
     *
     * @param array $data
     * @return bool
     */
    public function mealPlanExist(array $data)
    {
        return MealPlan::where('date', $data['date'])->where('meal_id', $data['meal_id'])->exists();
    }
}
