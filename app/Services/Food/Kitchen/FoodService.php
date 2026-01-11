<?php

namespace App\Services\Food\Kitchen;

use App\Models\Food\Food;
use App\Repositories\Food\Kitchen\FoodRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class FoodService
{
    /**
     * @var foodRepository
     */
    protected $foodRepository;

    /**
     * FoodService constructor
     *
     * @param FoodRepository $foodRepository
     */
    public function __construct(FoodRepository $foodRepository)
    {
        $this->foodRepository = $foodRepository;
    }

    /**
     * create new food
     *
     * @param array $data
     * @return \App\Models\Food\Food
     */
    public function createFood($request)
    {
        // Check for Food existence with same name
        if ($this->foodRepository->foodExist(
            $request
        )) {
            throw ValidationException::withMessages([
                'food_exist' => ['این غذا، در پایگاه داده وجود دارد.']
            ]);
        }
        $food = $this->foodRepository->create($request);
        return $this->formatFoodPayload($food);
    }

    /**
     * Get all foods
     *
     * @return array
     */
    public function getAllFoods()
    {
        $foods = $this->foodRepository->getAll();
        return $this->formatFoodsPayload($foods);
    }

    /**
     * Get all active foods
     *
     * @return array
     */
    public function getAllActiveFoods()
    {
        $foods = $this->foodRepository->getAllActive();
        return $this->formatFoodsPayload($foods);
    }

    /**
     * Update food
     *
     * @param int $id
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function updateFood(int $id, array $data)
    {
        $food = $this->foodRepository->update($id, $data);
        return $this->formatFoodPayload($food);
    }

    /**
     * Delete food
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->foodRepository->delete($id);
    }

    public function changeStatus(int $id)
    {
        $food = $this->foodRepository->status($id);
        if ($food) {
            return $this->formatFoodPayload($food);
        }
        return null;
    }

    /**
     * Format single food payload
     *
     * @param Food $food
     * @return array
     */
    protected function formatFoodPayload(Food $food): array
    {
        return [
            'id' => $food->id,
            'name' => $food->name,
            'status' => $food->status,
            'price' => $food->price,
            'createdBy' => $food->createdBy ? [
                'id' => $food->createdBy->id,
                'fullName' => $food->createdBy->first_name . ' ' . $food->createdBy->last_name,
                'username' => $food->createdBy->username,
            ] : null,
            'editedBy' => $food->editedBy ? [
                'id' => $food->editedBy->id,
                'fullName' => $food->editedBy->first_name . ' ' . $food->editedBy->last_name,
                'username' => $food->editedBy->username,
            ] : null,
            'createdAt' => $food->created_at,
            'updatedAt' => $food->updated_at,
        ];
    }

    /**
     * Format foods list payload
     *
     * @param \Illuminate\Database\Eloquent\Collection $foods
     * @return array
     */
    protected function formatFoodsPayload($foods): array
    {
        return [
            'foods' => $foods->map(function ($food) {
                return $this->formatFoodPayload($food);
            })->toArray(),
            'metadata' => [
                'total' => $foods->count(),
                'retrievedAt' => Carbon::now(),
            ],
        ];
    }
}
