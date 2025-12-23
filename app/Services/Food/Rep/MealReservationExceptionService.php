<?php

namespace App\Services\Food\Rep;

use App\Models\Food\MealReservationException;
use App\Repositories\Food\Rep\MealReservationExceptionRepository;
use Carbon\Carbon;

class MealReservationExceptionService
{
    /**
     * @var mealReservationExceptionRepository
     */
    protected $mealReservationExceptionRepository;

    /**
     * MealReservationExceptionService constructor
     *
     * @param MealReservationExceptionRepository $mealReservationExceptionRepository
     */
    public function __construct(MealReservationExceptionRepository $mealReservationExceptionRepository)
    {
        $this->mealReservationExceptionRepository = $mealReservationExceptionRepository;
    }

    /**
     * create new Meal Reservation Exception
     *
     * @param array $data
     * @return \App\Models\Food\MealReservationException
     */
    public function createMealReservationException($request)
    {
        $created = [];

        foreach ($request['users'] as $userId) {
            $exists = $this->mealReservationExceptionRepository->mealReservationExceptionExist(
                $userId,
                $request['meal_id']
            );

            if (!$exists) {
                $data = [
                    'user_id' => $userId,
                    'meal_id' => $request['meal_id'],
                ];

                $exception = $this->mealReservationExceptionRepository->create($data);
                $created[] = $this->formatMealReservationExceptionPayload($exception);
            }
        }

        return $created;
    }

    /**
     * Get all Meal Reservation Exceptions
     *
     * @return array
     */
    public function getAllMealReservationExceptions()
    {
        $mealReservationExceptions = $this->mealReservationExceptionRepository->getAll();
        return $this->formatMealReservationExceptionsListPayload($mealReservationExceptions);
    }

    /**
     * Get all active Meal Reservation Exceptions
     *
     * @return array
     */
    public function getAllActiveMealReservationExceptions()
    {
        $mealReservationExceptions = $this->mealReservationExceptionRepository->getAll();
        return $this->formatMealReservationExceptionsListPayload($mealReservationExceptions);
    }

    /**
     * Delete Meal Reservation Exception
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->mealReservationExceptionRepository->delete($id);
    }

    /**
     * change status of a Meal Reservation Exception
     *
     * @param int $id
     * @return MealReservationException|null
     */
    public function changeStatus(int $id)
    {
        $mealReservationException = $this->mealReservationExceptionRepository->status($id);
        if ($mealReservationException) {
            return $this->formatMealReservationExceptionPayload($mealReservationException);
        }
        return null;
    }

    /**
     * Format single Meal Reservation Exception
     *
     * @param MealReservationException $mealReservationException
     * @return array
     */
    protected function formatMealReservationExceptionPayload(MealReservationException $mealReservationException): array
    {
        return [
            'id' => $mealReservationException->id,
            'user' => $mealReservationException->user ? [
                'id' => $mealReservationException->user->id,
                'fullName' => $mealReservationException->user->first_name . ' ' . $mealReservationException->user->last_name,
                'username' => $mealReservationException->user->username,
            ] : null,
            'meal' => $mealReservationException->meal ? [
                'id' => $mealReservationException->meal->id,
                'name' => $mealReservationException->meal->name,
            ] : null,
            'status' => $mealReservationException->status,
            'createdBy' => $mealReservationException->createdBy ? [
                'id' => $mealReservationException->createdBy->id,
                'fullName' => $mealReservationException->createdBy->first_name . ' ' . $mealReservationException->createdBy->last_name,
                'username' => $mealReservationException->createdBy->username,
            ] : null,
            'editedBy' => $mealReservationException->editedBy ? [
                'id' => $mealReservationException->editedBy->id,
                'fullName' => $mealReservationException->editedBy->first_name . ' ' . $mealReservationException->editedBy->last_name,
                'username' => $mealReservationException->editedBy->username,
            ] : null,
            'createdAt' => $mealReservationException->created_at,
            'updatedAt' => $mealReservationException->updated_at,
        ];
    }

    /**
     * Format Meal Reservation Exception list payload
     *
     * @param \Illuminate\Database\Eloquent\Collection $mealReservationExceptions
     * @return array
     */
    protected function formatMealReservationExceptionsListPayload($mealReservationExceptions): array
    {
        return [
            'mealReservationExceptions' => $mealReservationExceptions->map(function ($mealReservationException) {
                return $this->formatMealReservationExceptionPayload($mealReservationException);
            })->toArray(),
            'metadata' => [
                'total' => $mealReservationExceptions->count(),
                'retrievedAt' => Carbon::now(),
            ],
        ];
    }
}
