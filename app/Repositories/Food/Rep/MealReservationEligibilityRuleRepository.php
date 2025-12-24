<?php

namespace App\Repositories\Food\Rep;

use Illuminate\Support\Facades\Auth;
use App\Models\Food\MealReservationEligibilityRule;

class MealReservationEligibilityRuleRepository
{
    /**
     * create new Meal Reservation Eligibility Rule
     *
     * @param array $data
     * @return \App\Models\Food\MealReservationEligibilityRule
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::id();
        return MealReservationEligibilityRule::create($data);
    }

    /**
     * Get all Meal Reservation Exception Eligibility Rules
     *
     * @return array
     */
    public function getAll()
    {
        return MealReservationEligibilityRule::get();
    }

    /**
     * Update Meal Reservation Exception Eligibility
     *
     * @param int $id
     * @param array $data
     * @return MealReservationEligibilityRule
     */
    public function update(int $id, array $data)
    {
        $mealReservationEligibilityRule = $this->findById($id);
        $data['edited_by'] = Auth::id();
        $mealReservationEligibilityRule->update($data);
        return $mealReservationEligibilityRule;
    }

    /**
     * Delete Meal Reservation Eligibility Rule
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        $mealReservationEligibilityRule = $this->findById($id);
        return $mealReservationEligibilityRule->delete();
    }

    /**
     * Get by ID
     *
     * @param int $id
     * @return MealReservationEligibilityRule
     * @throws ModelNotFoundException
     */
    public function findById(int $id): MealReservationEligibilityRule
    {
        return MealReservationEligibilityRule::findOrFail($id);
    }

    /**
     * Check if there's a Meal Reservation Eligibility Rule with the same meal
     *
     * @param array $data
     * @return bool
     */
    public function mealReservationEligibilityExist($mealId)
    {
        return MealReservationEligibilityRule::where('meal_id', $mealId)->exists();
    }
}
