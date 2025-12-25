<?php

namespace App\Http\Controllers\Food\Rep;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Food\FindMealReservationReportRequest;
use App\Services\Food\Rep\MealReservationContradictionService;

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
     * get all delivered meal reservation details for resservation type personnel in date range and meal
     *
     * @param Request $request
     * @param FindMealReservationReportRequest $request
     * @return JsonResponse
     */
    public function index(FindMealReservationReportRequest $request)
    {
        try {
            $data = $this->mealReservationDetailService->reservationDetailsByDateRangeAndMeal($request->validated());

            $payload = [
                'data' => $data,
                'message' => 'اطلاعات با موفقیت دریافت شد.',
                'status' => 201,
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
