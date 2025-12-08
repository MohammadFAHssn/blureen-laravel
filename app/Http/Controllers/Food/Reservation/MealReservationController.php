<?php

namespace App\Http\Controllers\Food\Reservation;

use App\Http\Requests\Food\CreateMealReservationRequest;
use App\Http\Requests\Food\UpdateMealPlanRequest;
use App\Services\Food\Reservation\MealReservationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class MealReservationController
 *
 * Handles HTTP requests for MealReservation management
 *
 * @package App\Http\Controllers\Food\Reservation
 */
class MealReservationController
{
    /**
     * @var MealReservationService
     */
    protected $mealReservationService;

    /**
     * MealReservationController constructor
     *
     * @param MealReservationService $mealReservationService
     */
    public function __construct(MealReservationService $mealReservationService)
    {
        $this->mealReservationService = $mealReservationService;
    }

    /**
     * Store a new meal reservation
     *
     * @param Request $request
     * @param CreateMealReservationRequest $request
     * @return JsonResponse
     */
    public function store(CreateMealReservationRequest $request)
    {
        try {
            $data = $this->mealReservationService->createMealReservation($request->validated());

            $payload = [
                'data' => $data,
                'message' => 'رزرو با موفقیت انجام شد.',
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

    /**
     * Get all meal reservations for personnel by a user on date
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reservationsForPersonnelByUserOnDate(Request $request)
    {
        try {
            $data = $this->mealReservationService->getAllMealReservationsForPersonnelByUserOnDate($request);

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

    /**
     * Get all meal reservations for a user by others on date
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reservationsForUserByOthersOnDate(Request $request)
    {
        try {
            $data = $this->mealReservationService->getAllMealReservationsForUserByOthersOnDate($request);

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
