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
}
