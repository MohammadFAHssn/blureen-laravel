<?php

namespace App\Repositories\Food\Reservation;

use App\Models\Food\MealReservation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class MealReservationRepository
{
    /**
     * create new meal reservation
     *
     * @param array $data
     * @return \App\Models\Food\MealReservation
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::id();

        $maxAttempts = 200;

        // duplicate: loop and try another code
        for ($i = 0; $i < $maxAttempts; $i++) {
            $data['delivery_code'] = $this->generateDeliveyCode();

            try {
                return MealReservation::create($data);
            } catch (QueryException $e) {
                // MySQL duplicate key
                $isDuplicate =
                    $e->getCode() === '23000' ||
                    (($e->errorInfo[1] ?? null) === 1062);

                if (!$isDuplicate) {
                    // some other DB error
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Could not generate unique delivery code after ' . $maxAttempts . ' attempts.');
    }

    /**
     * Get all meal reservations
     *
     * @return array
     */
    public function getAll()
    {
        return MealReservation::with('createdBy', 'editedBy')->get();
    }

    /**
     * Get all meal reservations for a date
     *
     * @param array $data
     * @return array
     */
    public function getAllForDate($data)
    {
        return MealReservation::where('date', $data['date'])->with('createdBy', 'editedBy')->get();
    }

    /**
     * Get all meal reservations for a user on date
     *
     * @param array $data
     * @return array
     */
    public function getAllForUserOnDate($data)
    {
        return MealReservation::where('created_by', $data['user_id'])->where('date', $data['date'])->with('createdBy', 'editedBy')->get();
    }

    /**
     * Update meal reservation
     *
     * @param int $id
     * @param array $data
     * @return MealReservation
     */
    public function update(int $id, array $data)
    {
        $mealReservation = $this->findById($id);
        $data['edited_by'] = Auth::id();
        $mealReservation->update($data);
        return $mealReservation;
    }

    /**
     * Delete meal reservation
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        $mealReservation = $this->findById($id);
        return $mealReservation->delete();
    }

    /**
     * Get a meal reservation by ID
     *
     * @param int $id
     * @return MealReservation
     * @throws ModelNotFoundException
     */
    public function findById(int $id): MealReservation
    {
        return MealReservation::findOrFail($id);
    }

    /**
     * Generate Delivery Code
     *
     * @return int
     */
    public function generateDeliveyCode()
    {
        return random_int(1000, 9999);
    }
}
