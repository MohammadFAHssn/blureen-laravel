<?php

namespace App\Http\Controllers\Food\Kitchen;

use App\Http\Requests\Food\CreateMealPlanRequest;
use App\Http\Requests\Food\UpdateMealPlanRequest;
use App\Services\Food\Kitchen\MealPlanService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class MealPlanController
 *
 * Handles HTTP requests for MealPlan management
 *
 * @package App\Http\Controllers\Food\Kitchen
 */
class MealPlanController
{
    /**
     * @var MealPlanService
     */
    protected $mealPlanService;

    /**
     * MealPlanController constructor
     *
     * @param MealPlanService $mealPlanService
     */
    public function __construct(MealPlanService $mealPlanService)
    {
        $this->mealPlanService = $mealPlanService;
    }

    /**
     * Store a new meal plan
     *
     * @param Request $request
     * @param CreateMealPlanRequest $request
     * @return JsonResponse
     */
    public function store(CreateMealPlanRequest $request)
    {
        try {
            $data = $this->mealPlanService->createMealPlan($request->validated());

            $payload = [
                'data' => $data,
                'message' => 'برنامه غذایی با موفقیت ایجاد شد.',
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
     * Get all meal plans
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $data = $this->mealPlanService->getAllMealPlans();

            $payload = [
                'data' => $data,
                'message' => 'لیست برنامه‌های غذایی با موفقیت دریافت شد.',
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
     * Get all meal plans for date
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function plansForDate(Request $request)
    {
        try {
            $data = $this->mealPlanService->getAllMealPlansForDate($request);

            $payload = [
                'data' => $data,
                'message' => 'لیست برنامه‌های غذایی با موفقیت دریافت شد.',
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
     * Update a specific meal plan
     *
     * @param UpdateMealPlanRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateMealPlanRequest $request, int $id)
    {
        try {
            $data = $this->mealPlanService->updateMealPlan($id, $request->validated());

            $payload = [
                'data' => $data,
                'message' => 'برنامه غذایی با موفقیت بروزرسانی شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'برنامه غذایی مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'MEAL_PLAN_NOT_FOUND',
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
     * Delete meal plan
     *
     * @param Request $request
     * @return bool
     */
    public function delete(Request $request)
    {
        try {
            $data = $this->mealPlanService->delete($request->toArray());

            $payload = [
                'data' => $data,
                'message' => 'برنامه غذایی با موفقیت حذف شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'برنامه غذایی مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'MEAL_PLAN_NOT_FOUND',
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
