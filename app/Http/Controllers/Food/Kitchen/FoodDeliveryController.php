<?php

namespace App\Http\Controllers\Food\Kitchen;

use App\Http\Requests\Food\FindMealReservationRequest;
use App\Services\Food\Kitchen\FoodDeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class FoodDeliveryController
 *
 * Handles HTTP requests for food delivery management
 *
 * @package App\Http\Controllers\Food\Kitchen
 */
class FoodDeliveryController
{
    /**
     * @var FoodDeliveryService
     */
    protected $foodDeliveryService;

    /**
     * FoodDeliveryController constructor
     *
     * @param FoodDeliveryService $foodDeliveryService
     */
    public function __construct(FoodDeliveryService $foodDeliveryService)
    {
        $this->foodDeliveryService = $foodDeliveryService;
    }

    /**
     * find a undelivered meal reservation by date and delivery code
     *
     * @param Request $request
     * @param FindMealReservationRequest $request
     * @return JsonResponse
     */
    public function find(FindMealReservationRequest $request)
    {
        try {
            $data = $this->foodDeliveryService->findUndeliveredMealReservation($request->validated());

            $payload = [
                'data' => $data,
                'message' => 'وعده رزرو شده با موفقیت دریافت شد.',
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
     * Get all undelivered meal reservations on a date
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $data = $this->foodDeliveryService->getAllUndeliveredMealReservationsOnDate($request->toArray());

            $payload = [
                'data' => $data,
                'message' => 'لیست وعده‌های رزرو شده با موفقیت دریافت شد.',
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
