<?php

namespace App\Http\Controllers\Food\Rep;

use App\Http\Requests\Food\CreateMealReservationExceptionRequest;
use App\Services\Food\Rep\MealReservationExceptionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class MealReservationExceptionController
 *
 * Handles HTTP requests for Meal Reservation Exception
 *
 * @package App\Http\Controllers\Food\Rep
 */
class MealReservationExceptionController
{
    /**
     * @var MealReservationExceptionService
     */
    protected $mealReservationExceptionService;

    /**
     * MealController constructor
     *
     * @param MealReservationExceptionService $mealReservationExceptionService
     */
    public function __construct(MealReservationExceptionService $mealReservationExceptionService)
    {
        $this->mealReservationExceptionService = $mealReservationExceptionService;
    }

    /**
     * Get all Meal Reservation Exceptions
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $data = $this->mealReservationExceptionService->getAllMealReservationExceptions();

            $payload = [
                'data' => $data,
                'message' => 'لیست استثنائات با موفقیت دریافت شد.',
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
     * Get all active Meal Reservation Exceptions
     *
     * @return JsonResponse
     */
    public function getActives()
    {
        try {
            $data = $this->mealReservationExceptionService->getAllActiveMealReservationExceptions();

            $payload = [
                'data' => $data,
                'message' => 'لیست استثنائات با موفقیت دریافت شد.',
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
     * Store a new Meal Reservation Exception
     *
     * @param Request $request
     * @param CreateMealReservationExceptionRequest $request
     * @return JsonResponse
     */
    public function store(CreateMealReservationExceptionRequest $request)
    {
        try {
            $data = $this->mealReservationExceptionService->createMealReservationException($request->validated());

            $payload = [
                'data' => $data,
                'message' => 'استثناء با موفقیت ایجاد شد.',
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
     * Change Status of a Meal Reservation Exception
     *
     * @param int $id
     * @return JsonResponse
     */
    public function changeStatus(int $id)
    {
        try {
            $data = $this->mealReservationExceptionService->changeStatus($id);

            $payload = [
                'data' => $data,
                'message' => 'استثناء با موفقیت بروزرسانی شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'استثناء مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'RESERVATION_EXCEPTION_NOT_FOUND',
            ];

            return response()->json($payload)->setStatusCode($payload['status']);
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
     * Delete Meal Reservation Exception
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        try {
            $data = $this->mealReservationExceptionService->delete($id);

            $payload = [
                'data' => $data,
                'message' => 'استثناء با موفقیت حذف شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'استثناء مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'RESERVATION_EXCEPTION_NOT_FOUND',
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
