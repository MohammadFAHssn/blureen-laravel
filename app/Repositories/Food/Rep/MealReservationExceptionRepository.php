<?php

namespace App\Repositories\Food\Rep;

use Illuminate\Support\Facades\Auth;
use App\Models\Food\MealReservationException;

class MealReservationExceptionRepository
{
    /**
     * create new Meal Reservation Exception
     *
     * @param array $data
     * @return \App\Models\Food\MealReservationException
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::id();
        return MealReservationException::create($data);
    }

    /**
     * Get all Meal Reservation Exceptions
     *
     * @return array
     */
    public function getAll()
    {
        return MealReservationException::get();
    }

    /**
     * Get all Active Meal Reservation Exceptions
     *
     * @return array
     */
    public function getAllActive()
    {
        return MealReservationException::where('status', 1)->get();
    }

    /**
     * Delete Meal Reservation Exception
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        $meal = $this->findById($id);
        return $meal->delete();
    }

    /**
     * Get by ID
     *
     * @param int $id
     * @return MealReservationException
     * @throws ModelNotFoundException
     */
    public function findById(int $id): MealReservationException
    {
        return MealReservationException::findOrFail($id);
    }

    /**
     * Check if there's a Meal Reservation Exception with the same user and meal
     *
     * @param array $data
     * @return bool
     */
    public function mealReservationExceptionExist($userId, $mealId)
    {
        return MealReservationException::where('user_id', $userId)->where('meal_id', $mealId)->exists();
    }

    /**
     * change status of a Meal Reservation Exception
     *
     * @param int $id
     * @return MealReservationException|null
     */
    public function status(int $id)
    {
        $mealReservationException = $this->findById($id);
        if (!$mealReservationException) {
            return null;
        }

        $mealReservationException->status = !$mealReservationException->status;
        $mealReservationException->edited_by = Auth::id();
        $mealReservationException->save();

        return $mealReservationException;
    }
}
