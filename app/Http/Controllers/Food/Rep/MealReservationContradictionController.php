<?php

namespace App\Http\Controllers\Food\Rep;

use App\Http\Requests\Food\FindMealReservationReportRequest;
use App\Services\Food\Rep\MealReservationContradictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class MealReservationContradictionController
 *
 * Handles HTTP requests for Meal Reservation Contradiction management
 *
 * @package App\Http\Controllers\Food\Rep
 */
class MealReservationContradictionController
{
    /**
     * @var MealReservationContradictionService
     */
    protected $mealReservationDetailService;

    /**
     * MealReservationContradictionController constructor
     *
     * @param MealReservationContradictionService $mealReservationDetailService
     */
    public function __construct(MealReservationContradictionService $mealReservationDetailService)
    {
        $this->mealReservationDetailService = $mealReservationDetailService;
    }

    /**
     * Retrieve delivered meal reservation details for personnel reservations within a date range,
     * filtered to personnel who did NOT stay overtime (non-entitled), for a specific meal.
     *
     * Request expects:
     * - date: [from, to]
     * - meal_id
     *
     * @param  \App\Http\Requests\Food\Rep\FindMealReservationReportRequest  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function index(FindMealReservationReportRequest $request)
    {
        try {
            $data = $this->mealReservationDetailService->nonEntitledReservationDetailsByDateRangeAndMeal($request->validated());

            $payload = [
                'data' => $data,
                'message' => 'اطلاعات با موفقیت دریافت شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ValidationException $e) {
            $payload = [
                'errors' => $e->errors(),
                'message' => 'اطلاعات وارد شده معتبر نیست.',
                'status' => 422,
                'code' => 'VALIDATION_ERROR',
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
