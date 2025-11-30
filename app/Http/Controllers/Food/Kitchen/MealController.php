<?php

namespace App\Http\Controllers\Food\Kitchen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Food\CreateMealRequest;
use App\Http\Requests\Food\UpdateMealRequest;
use App\Services\Food\Kitchen\MealService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class MealController
 *
 * Handles HTTP requests for Meal management
 *
 * @package App\Http\Controllers\Food\Kitchen
 */
class MealController
{
    /**
     * @var MealService
     */
    protected $mealService;

    /**
     * MealController constructor
     *
     * @param MealService $mealService
     */
    public function __construct(MealService $mealService)
    {
        $this->mealService = $mealService;
    }

    /**
     * Store a new meal
     *
     * @param Request $request
     * @param CreateMealRequest $request
     * @return JsonResponse
     */
    public function store(CreateMealRequest $request)
    {
        try {
            $data = $this->mealService->createMeal($request->validated());

            $payload = [
                'data' => $data,
                'message' => 'وعده جدید با موفقیت ایجاد شد.',
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
     * Get all meals
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $data = $this->mealService->getAllMeals();

            $payload = [
                'data' => $data,
                'message' => 'لیست وعده‌ها با موفقیت دریافت شد.',
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
     * Get all active meals
     *
     * @return JsonResponse
     */
    public function getActives()
    {
        try {
            $data = $this->mealService->getAllActiveMeals();

            $payload = [
                'data' => $data,
                'message' => 'لیست وعده‌ها با موفقیت دریافت شد.',
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
     * Update a specific meal
     *
     * @param UpdateMealRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateMealRequest $request, int $id)
    {
        try {
            $data = $this->mealService->updateMeal($id, $request->validated());

            $payload = [
                'data' => $data,
                'message' => 'وعده با موفقیت بروزرسانی شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'وعده مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'MEAL_NOT_FOUND',
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
     * Delete meal
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        try {
            $data = $this->mealService->delete($id);

            $payload = [
                'data' => $data,
                'message' => 'وعده با موفقیت حذف شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'وعده مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'MEAL_NOT_FOUND',
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
     * Change Status of a meal
     *
     * @param int $id
     * @return JsonResponse
     */
    public function changeStatus(int $id)
    {
        try {
            $data = $this->mealService->changeStatus($id);

            $payload = [
                'data' => $data,
                'message' => 'وعده با موفقیت بروزرسانی شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'وعده مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'MEAL_NOT_FOUND',
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
}
