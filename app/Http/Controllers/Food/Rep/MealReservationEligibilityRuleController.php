<?php

namespace App\Http\Controllers\Food\Rep;

use App\Http\Requests\Food\CreateMealReservationEligibilityRuleRequest;
use App\Http\Requests\Food\UpdateMealReservationEligibilityRuleRequest;
use App\Services\Food\Rep\MealReservationEligibilityRuleService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class MealReservationEligibilityRuleController
 *
 * Handles HTTP requests for Meal Reservation Eligibility Rule management
 *
 * @package App\Http\Controllers\Food\Rep
 */
class MealReservationEligibilityRuleController
{
    /**
     * @var MealReservationEligibilityRuleService
     */
    protected $mealReservationEligibilityRuleService;

    /**
     * MealReservationEligibilityRuleController constructor
     *
     * @param MealReservationEligibilityRuleService $mealReservationEligibilityRuleService
     */
    public function __construct(MealReservationEligibilityRuleService $mealReservationEligibilityRuleService)
    {
        $this->mealReservationEligibilityRuleService = $mealReservationEligibilityRuleService;
    }

    /**
     * Get all Meal Reservation Eligibility Rules
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $data = $this->mealReservationEligibilityRuleService->getAllMealReservationEligibilityRules();

            $payload = [
                'data' => $data,
                'message' => 'لیست محدودیت‌های زمانی با موفقیت دریافت شد.',
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
     * Store a new Meal Reservation Eligibility Rule
     *
     * @param Request $request
     * @param CreateMealReservationEligibilityRuleRequest $request
     * @return JsonResponse
     */
    public function store(CreateMealReservationEligibilityRuleRequest $request)
    {
        try {
            $data = $this->mealReservationEligibilityRuleService->createMealReservationEligibilityRule($request->validated());

            $payload = [
                'data' => $data,
                'message' => 'محدودیت زمانی با موفقیت ذخیره شد.',
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
     * Update a specific Meal Reservation Eligibility Rule
     *
     * @param UpdateMealReservationEligibilityRuleRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateMealReservationEligibilityRuleRequest $request, int $id)
    {
        try {
            $data = $this->mealReservationEligibilityRuleService->updateMealReservationEligibilityRule($id, $request->validated());

            $payload = [
                'data' => $data,
                'message' => 'محدودیت زمانی با موفقیت بروزرسانی شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'محدودیت زمانی مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'MEAL_RESERVATION_ELIGIBILITY_RULE_NOT_FOUND',
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
            $data = $this->mealReservationEligibilityRuleService->delete($id);

            $payload = [
                'data' => $data,
                'message' => 'محدودیت زمانی با موفقیت حذف شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'محدودیت زمانی مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'MEAL_RESERVATION_ELIGIBILITY_RULE_NOT_FOUND',
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
