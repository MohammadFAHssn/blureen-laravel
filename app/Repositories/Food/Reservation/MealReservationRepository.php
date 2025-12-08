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
     * Get all meal reservations for personnel by a user on a date
     *
     * @param array $data
     * @return array
     */
    public function getAllForPersonnelByUserOnDate($data)
    {
        $id = Auth::id();
        return MealReservation::personnel()->where('date', $data)->where('created_by', $id)->with('meal', 'details', 'createdBy', 'editedBy')->get();
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
     * Get all meal reservations by date
     *
     * @param $date
     */
    public function findByDate($date)
    {
        return MealReservation::where('date', $date)->get();
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
