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

    /**
     * Get all meal reservations for contractor by a user on date
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reservationsForContractorByUserOnDate(Request $request)
    {
        try {
            $data = $this->mealReservationService->getAllMealReservationsForContractorByUserOnDate($request);

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
     * Get all meal reservations for guest by a user on date
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reservationsForGuestByUserOnDate(Request $request)
    {
        try {
            $data = $this->mealReservationService->getAllMealReservationsForGuestByUserOnDate($request);

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
     * Get all meal reservations for repairman by a user on date
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reservationsForRepairmanByUserOnDate(Request $request)
    {
        try {
            $data = $this->mealReservationService->getAllMealReservationsForRepairmanByUserOnDate($request);

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
     * Get all delivered meal reservations for a specific contractor in a date range
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deliveredReservationsForContractorOnDate(Request $request)
    {
        try {
            $data = $this->mealReservationService->getAllDeliveredMealReservationsForContractorOnDate($request->toArray());

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
     * Get all meal reservations in a date range
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reservationsInDateRange(Request $request)
    {
        try {
            $data = $this->mealReservationService->getAllMealReservationsInDateRange($request->toArray());

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
     * check to see if there is even one meal reservation in a date
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkForDelivered(Request $request)
    {
        try {
            $data = $this->mealReservationService->checkForDelivered($request->toArray());

            $payload = [
                'data' => $data,
                'message' => 'چک با موفقیت انجام شد.',
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
     * Delete meal reservation
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        try {
            $data = $this->mealReservationService->delete($id);

            $payload = [
                'data' => $data,
                'message' => 'رزرو با موفقیت حذف شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'رزرو مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'RESERVE_NOT_FOUND',
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
}
