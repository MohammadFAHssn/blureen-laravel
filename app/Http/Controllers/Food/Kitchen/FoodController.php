<?php

namespace App\Http\Controllers\Food\Kitchen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Food\CreateFoodRequest;
use App\Http\Requests\Food\UpdateFoodRequest;
use App\Services\Food\Kitchen\FoodService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

use function Laravel\Prompts\info;

/**
 * Class FoodController
 *
 * Handles HTTP requests for Food management
 *
 * @package App\Http\Controllers\Food\Kitchen
 */
class FoodController
{
    /**
     * @var FoodService
     */
    protected $foodService;

    /**
     * BirthdayGiftController constructor
     *
     * @param FoodService $foodService
     */
    public function __construct(FoodService $foodService)
    {
        $this->foodService = $foodService;
    }

    /**
     * Store a new food
     *
     * @param Request $request
     * @param CreateFoodRequest $request
     * @return JsonResponse
     */
    public function store(CreateFoodRequest $request)
    {
        try {
            $data = $this->foodService->createFood($request->validated());

            $payload = [
                'data' => $data,
                'message' => 'غذای جدید با موفقیت ایجاد شد.',
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
     * Get all foods
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $data = $this->foodService->getAllFoods();

            $payload = [
                'data' => $data,
                'message' => 'لیست غذاها با موفقیت دریافت شد.',
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
     * Get all active foods
     *
     * @return JsonResponse
     */
    public function getActives()
    {
        try {
            $data = $this->foodService->getAllActiveFoods();

            $payload = [
                'data' => $data,
                'message' => 'لیست غذاها با موفقیت دریافت شد.',
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
     * Update a specific food
     *
     * @param UpdateFoodRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateFoodRequest $request, int $id)
    {
        try {
            $data = $this->foodService->updateFood($id, $request->validated());

            $payload = [
                'data' => $data,
                'message' => 'غذا با موفقیت بروزرسانی شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'غذا مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'FOOD_NOT_FOUND',
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
     * Delete food
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        try {
            $data = $this->foodService->delete($id);

            $payload = [
                'data' => $data,
                'message' => 'غذا با موفقیت حذف شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'غذا مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'FOOD_NOT_FOUND',
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
     * Change Status of a food
     *
     * @param int $id
     * @return JsonResponse
     */
    public function changeStatus(int $id)
    {
        try {
            $data = $this->foodService->changeStatus($id);

            $payload = [
                'data' => $data,
                'message' => 'غذا با موفقیت بروزرسانی شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'غذا مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'FOOD_NOT_FOUND',
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
