<?php

namespace App\Http\Controllers\Food\Reservation;

use App\Services\Food\Reservation\MealReservationDetailService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class MealReservationDetailController
 *
 * Handles HTTP requests for MealReservationDetail management
 *
 * @package App\Http\Controllers\Food\Reservation
 */
class MealReservationDetailController
{
    /**
     * @var MealReservationDetailService
     */
    protected $mealReservationDetailService;

    /**
     * MealReservationDetailController constructor
     *
     * @param MealReservationDetailService $mealReservationDetailService
     */
    public function __construct(MealReservationDetailService $mealReservationDetailService)
    {
        $this->mealReservationDetailService = $mealReservationDetailService;
    }

    /**
     * Delete meal reservation detail
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        try {
            $data = $this->mealReservationDetailService->delete($id);

            $payload = [
                'data' => $data,
                'message' => 'حذف با موفقیت انجام شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'آیتم مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'DETAIL_NOT_FOUND',
            ];

            return response()->json($payload)->setStatusCode($payload['status']);
        } catch (Throwable $e) {
            $payload = [
                'error' => $e->getMessage(),
                'status' => 500,
            ];

            return response()->json($payload, $payload['status']);
        }
    }

    /**
     * Get all delivered meal reservations details for a specific contractor in a date range
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deliveredReservationsForContractorOnDate(Request $request)
    {
        try {
            $data = $this->mealReservationDetailService->getAllDeliveredMealReservationsForContractorOnDate($request->toArray());

            $payload = [
                'data' => $data,
                'message' => 'لیست رزروها با موفقیت دریافت شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (Throwable $e) {
            $payload = [
                'error' => $e->getMessage(),
                'status' => 500,
            ];

            return response()->json($payload, $payload['status']);
        }
    }
}
