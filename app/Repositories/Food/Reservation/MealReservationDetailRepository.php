<?php

namespace App\Repositories\Food\Reservation;

use App\Models\Food\MealReservationDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

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
        } else {
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
     * Get a meal reservation detail by meal reservation ID
     *
     * @param int $mealReservationId
     * @return MealReservationDetail
     * @throws ModelNotFoundException
     */
    public function findByMealReservationId(int $mealReservationId): MealReservationDetail
    {
        return MealReservationDetail::where('meal_reservation_id', $mealReservationId)->first();
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
                $q
                    ->whereDate('date', $date)
                    ->where('meal_id', $mealId);
            })
            ->pluck('reserved_for_personnel')
            ->unique()
            ->values();
    }

    /**
     * Get delivered meal reservation details for personnel reservations for a meal within a date range
     * where personnel did NOT stay overtime.
     *
     * Non-entitled = check_out_time is null OR check_out_time < cutoff time.
     * Exceptions = excluded personnel user IDs.
     *
     * @param string $from Start date
     * @param string $to End date
     * @param int $mealId
     * @param string $cutoffTime Time in HH:MM:SS
     * @param int[] $exceptionUserIds
     * @return \Illuminate\Support\Collection<int,\App\Models\MealReservationDetail>
     */
    public function nonEntitledDeliveredReservationDetailsByDateRangeAndMeal(string $from, string $to, int $mealId, string $cutoffTime, array $exceptionUserIds = [])
    {
        return MealReservationDetail::query()
            ->whereNotNull('reserved_for_personnel')
            ->where('delivery_status', 1)
            ->where(function ($q) use ($cutoffTime) {
                $q
                    ->whereNull('check_out_time')
                    ->orWhere('check_out_time', '<', $cutoffTime);
            })
            ->when(!empty($exceptionUserIds), fn($q) => $q->whereNotIn('reserved_for_personnel', $exceptionUserIds))
            ->whereHas('reservation', function ($q) use ($mealId, $from, $to) {
                $q
                    ->whereBetween('date', [$from, $to])
                    ->where('meal_id', $mealId)
                    ->where('reserve_type', 'personnel')
                    ->where('status', 1);
            })
            ->with('food', 'reservation', 'personnel')
            ->get();
    }

    /**
     * Get delivered personnel reservation details that still need a checkout lookup.
     *
     * Criteria:
     * - delivery_status = 1
     * - check_out_time is NULL
     * - last_check_at is NULL OR last_check_at is older than 2 days
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\MealReservationDetail>
     */
    public function personnelReservationDetailsNeedingCheckoutCheck()
    {
        return MealReservationDetail::query()
            ->where('delivery_status', 1)
            ->whereNull('check_out_time')
            ->where(function ($q) {
                $q
                    ->whereNull('last_check_at')
                    ->orWhere('last_check_at', '<', now()->subDays(2)->toDateString());
            })
            ->get();
    }

    /**
     * Get unique personnel user IDs for an unDelivered reservation's details (optionally excluding detail IDs).
     *
     * @param  int    $reservationId
     * @param  int[]  $excludeDetailIds
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function personnelIds(int $reservationId, array $excludeDetailIds = []): Collection
    {
        return MealReservationDetail::query()
            ->where('meal_reservation_id', $reservationId)
            ->where('delivery_status', 0)
            ->whereNotNull('reserved_for_personnel')
            ->when($excludeDetailIds, fn($q) => $q->whereNotIn('id', $excludeDetailIds))
            ->pluck('reserved_for_personnel')
            ->unique()
            ->values();
    }

    /**
     * Get all delivered meal reservations details for a specific contractor in a date range
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
        return MealReservationDetail::
            where('reserved_for_contractor', $contractorId)
            ->where('delivery_status', 1)
            ->whereHas('reservation', function ($q) use ($from, $to) {
                $q->whereBetween('date', [$from, $to])
                ->where('reserve_type', 'contractor')
                ->where('status', 1);
            })
            ->with('food', 'reservation', 'contractor', 'createdBy', 'editedBy')
            ->get();
    }
}
