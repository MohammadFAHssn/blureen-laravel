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
     * Get all meal reservations for contractor by a user on a date
     *
     * @param array $data
     * @return array
     */
    public function getAllForContractorByUserOnDate($data)
    {
        $id = Auth::id();
        return MealReservation::contractor()->where('date', $data)->where('created_by', $id)->with('meal', 'details', 'createdBy', 'editedBy')->get();
    }

    /**
     * Get all meal reservations for guest by a user on a date
     *
     * @param array $data
     * @return array
     */
    public function getAllForGuestByUserOnDate($data)
    {
        $id = Auth::id();
        return MealReservation::guest()->where('date', $data)->where('created_by', $id)->with('meal', 'details', 'createdBy', 'editedBy')->get();
    }

    /**
     * Get all meal reservations for repairman by a user on a date
     *
     * @param array $data
     * @return array
     */
    public function getAllForRepairmanByUserOnDate($data)
    {
        $id = Auth::id();
        return MealReservation::repairman()->where('date', $data)->where('created_by', $id)->with('meal', 'details', 'createdBy', 'editedBy')->get();
    }

    /**
     * Get all meal reservations on a date
     *
     * @param array $data
     * @return array
     */
    public function getAllOnDate($data)
    {
        return MealReservation::where('date', $data)->with('meal', 'supervisor', 'details', 'createdBy', 'editedBy')->get();
    }

    /**
     * Get all delivered meal reservations for a specific contractor in a date range
     *
     * @param string $from
     * @param string $to
     * @param int $contractorId
     * @return \Illuminate\Support\Collection
     */
    public function getAllDeliveredForContractorBetweenDates(
        string $from,
        string $to,
        int $contractorId
    ) {
        return MealReservation::contractor()
            ->where('status', 1)  // delivered
            ->whereBetween('date', [$from, $to])
            ->whereHas('details', function ($q) use ($contractorId) {
                $q->where('reserved_for_contractor', $contractorId);
            })
            ->with('meal', 'details', 'createdBy', 'editedBy')
            ->get();
    }

    /**
     * Get all meal reservations in a date range
     *
     * @param array $data
     * @return \Illuminate\Support\Collection
     */
    public function getAllBetweenDates(
        string $from,
        string $to
    ) {
        return MealReservation::
            whereBetween('date', [$from, $to])
            ->with('meal', 'details', 'createdBy', 'editedBy')
            ->get();
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
        if (!$mealReservation->status) {
            return $mealReservation->delete();
        } else {
            return false;
        }
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
     * find a meal reservation by date and delivery code.
     *
     * @param array $data
     * @return MealReservation|null
     */
    public function findByDateAndDeliveryCode(array $data): ?MealReservation
    {
        return MealReservation::where('date', $data['date'])
            ->where('delivery_code', $data['delivery_code'])
            ->with('meal', 'supervisor', 'details', 'createdBy', 'editedBy')
            ->first();
    }

    /**
     * find an undelivered meal reservation by it's id and deliver it(turn it's status on).
     *
     * @param int $id
     * @return MealReservation
     */
    public function deliver(int $id)
    {
        $mealReservation = $this->findById($id);
        $data['edited_by'] = Auth::id();
        $mealReservation->status = 1;
        $mealReservation->save();
        return $mealReservation;
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
