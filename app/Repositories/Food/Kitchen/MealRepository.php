<?php

namespace App\Repositories\Food\Kitchen;

use App\Models\Food\Meal;
use Illuminate\Support\Facades\Auth;

class MealRepository
{
    /**
     * create new meal
     *
     * @param array $data
     * @return \App\Models\Food\Meal
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::id();
        return Meal::create($data);
    }

    /**
     * Get all meals
     *
     * @return array
     */
    public function getAll()
    {
        return Meal::with('createdBy', 'editedBy')->get();
    }

    /**
     * Get all active meals
     *
     * @return array
     */
    public function getAllActive()
    {
        return Meal::with('createdBy', 'editedBy')->where('status', 1)->get();
    }

    /**
     * Update meal
     *
     * @param int $id
     * @param array $data
     * @return Meal
     */
    public function update(int $id, array $data)
    {
        $meal = $this->findById($id);
        $data['edited_by'] = Auth::id();
        $meal->update($data);
        return $meal;
    }

    /**
     * Delete meal
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
     * Get a by ID
     *
     * @param int $id
     * @return Meal
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Meal
    {
        return Meal::findOrFail($id);
    }

    /**
     * Check if there's a meal with the same name
     *
     * @param array $data
     * @return bool
     */
    public function mealExist(array $data)
    {
        return Meal::where('name', $data['name'])->exists();
    }

    public function status(int $id)
    {
        $meal = $this->findById($id);
        if (!$meal) {
            return null;
        }

        $meal->status = !$meal->status;
        $meal->edited_by = Auth::id();
        $meal->save();

        return $meal;
    }
}
