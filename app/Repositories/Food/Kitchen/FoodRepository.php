<?php

namespace App\Repositories\Food\Kitchen;

use App\Models\Food\Food;
use Illuminate\Support\Facades\Auth;

class FoodRepository
{
    /**
     * create new food
     *
     * @param array $data
     * @return \App\Models\Food\Food
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::id();
        return Food::create($data);
    }

    /**
     * Get all foods
     *
     * @return array
     */
    public function getAll()
    {
        return Food::with('createdBy', 'editedBy')->get();
    }

    /**
     * Get all active foods
     *
     * @return array
     */
    public function getAllActive()
    {
        return Food::with('createdBy', 'editedBy')->where('status', 1)->get();
    }

    /**
     * Update food
     *
     * @param int $id
     * @param array $data
     * @return Food
     */
    public function update(int $id, array $data)
    {
        $food = $this->findById($id);
        $data['edited_by'] = Auth::id();
        $food->update($data);
        return $food;
    }

    /**
     * Delete food
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        $food = $this->findById($id);
        return $food->delete();
    }

    /**
     * Get food by ID
     *
     * @param int $id
     * @return Food
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Food
    {
        return Food::findOrFail($id);
    }

    /**
     * Check if there's a food with the same name
     *
     * @param array $data
     * @return bool
     */
    public function foodExist(array $data)
    {
        return Food::where('name', $data['name'])->exists();
    }

    public function status(int $id)
    {
        $food = $this->findById($id);
        if (!$food) {
            return null;
        }

        $food->status = !$food->status;
        $food->edited_by = Auth::id();
        $food->save();

        return $food;
    }
}
