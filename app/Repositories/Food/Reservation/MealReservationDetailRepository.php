<?php

namespace App\Repositories\Food\Reservation;

use Illuminate\Support\Facades\Auth;
use App\Models\Food\MealReservationDetail;

class MealReservationDetailRepository
{
    /**
     * create new meal reservation detail
     *
     * @param array $data
     * @return \App\Models\Food\MealReservationDetail
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::id();
        return MealReservationDetail::create($data);
    }

    /**
     * Get meal reservation detail by reservation Id and user Id
     *
     * @param int $id
     * @return collection
     */
    public function findByReservationIdUserId(int $reservationId)
    {
        $userId = Auth::id();
        return MealReservationDetail::where('meal_reservation_id', $reservationId)->where('reserved_for_personnel', $userId)->with('reservation', 'createdBy', 'editedBy')->get();
    }

    /**
     * Mark undelivered reservation details as delivered, excluding specific detail IDs.
     *
     * @param  int    $reservationId
     * @param  int[]  $excludeDetailIds
     * @return int  Number of updated rows.
     */
    public function markDeliveredExcept(int $reservationId, array $excludeDetailIds = []): int
    {
        return MealReservationDetail::query()
            ->where('meal_reservation_id', $reservationId)
            ->where('delivery_status', false)
            ->when(!empty($excludeDetailIds), fn($q) => $q->whereNotIn('id', $excludeDetailIds))
            ->update(['delivery_status' => true]);
    }

    /**
     * Update meal reservation detail based on meal reservation id
     *
     * @param int $reservationId
     * @param array $data
     * @return MealReservationDetail
     */
    public function update(int $reservationId, array $data)
    {
        $mealReservationDetail = MealReservationDetail::where('meal_reservation_id', $reservationId)->first();
        $data['edited_by'] = Auth::id();
        $mealReservationDetail->update($data);
        return $mealReservationDetail;
    }

    /**
     * Delete meal reservation detail
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        $mealReservationDetail = $this->findById($id);
        if (!$mealReservationDetail->delivery_status && !$mealReservationDetail->reservation->status) {
            return $mealReservationDetail->delete();
        }
        else {
            return false;
        }
    }

    /**
     * Get a meal reservation detail by ID
     *
     * @param int $id
     * @return MealReservationDetail
     * @throws ModelNotFoundException
     */
    public function findById(int $id): MealReservationDetail
    {
        return MealReservationDetail::findOrFail($id);
    }

    /**
     * Get unique personnel IDs for reserved meal for a given date and meal.
     *
     * @param  string|\Carbon\Carbon  $date
     * @param  int                    $mealId
     * @return \Illuminate\Support\Collection<int>
     */
    public function reservedPersonnelIdsByDateAndMeal($date, int $mealId)
    {
        return MealReservationDetail::query()
            ->whereNotNull('reserved_for_personnel')
            ->whereHas('reservation', function ($q) use ($date, $mealId) {
                $q->whereDate('date', $date)
                ->where('meal_id', $mealId);
            })
            ->pluck('reserved_for_personnel')
            ->unique()
            ->values();
    }

    /**
     * Get delivered meal reservation details for reservation type personnel for a given meal within a date range.
     *
     * Returns a collection of MealReservationDetail models where:
     * - reserved_for_personnel is not null
     * - delivery_status is delivered (1)
     * - the parent reservation matches the given meal, date range, and is an active personnel reservation
     *
     * @param  string  $from  Start date (Y-m-d)
     * @param  string  $to    End date (Y-m-d)
     * @param  int     $mealId
     * @return \Illuminate\Support\Collection<int, \App\Models\MealReservationDetail>
     */
    public function deliveredPersonnelReservationDetailsByDateRangeAndMeal(string $from, string $to, int $mealId)
    {
        return MealReservationDetail::query()
            ->whereNotNull('reserved_for_personnel')
            ->where('delivery_status', 1)
            ->whereHas('reservation', function ($q) use ($mealId, $from, $to) {
                $q->whereBetween('date', [$from, $to])
                ->where('meal_id', $mealId)
                ->where('reserve_type', 'personnel')
                ->where('status', 1);
            })
            ->with('food', 'reservation', 'personnel')
            ->get();
    }

}
