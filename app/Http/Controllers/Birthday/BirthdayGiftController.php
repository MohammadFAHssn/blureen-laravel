<?php

namespace App\Http\Controllers\Birthday;

use App\Http\Controllers\Controller;
use App\Http\Requests\Birthday\CreateBirthdayGiftRequest;
use App\Http\Requests\Birthday\UpdateBirthdayGiftRequest;
use App\Services\Birthday\BirthdayGiftService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class BirthdayGiftController
 *
 * Handles HTTP requests for BirthdayGift management
 *
 * @package App\Http\Controllers\Birthday
 */
class BirthdayGiftController
{
    /**
     * @var BirthdayGiftService
     */
    protected $birthdayGiftService;

    /**
     * BirthdayGiftController constructor
     *
     * @param BirthdayGiftService $birthdayGiftService
     */
    public function __construct(BirthdayGiftService $birthdayGiftService)
    {
        $this->birthdayGiftService = $birthdayGiftService;
    }

    /**
     * Store a new birthday gift
     *
     * @param Request $request
     * @param CreateBirthdayGiftRequest $request
     * @return JsonResponse
     */
    public function store(CreateBirthdayGiftRequest $request)
    {
        try {
            $data = $this->birthdayGiftService->createBirthdayGift($request->validated());

            $payload = [
                'data' => $data,
                'message' => 'هدیه جدید با موفقیت ایجاد شد.',
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
     * Get all birthday gifts
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $data = $this->birthdayGiftService->getAllBirthdayGifts();

            $payload = [
                'data' => $data,
                'message' => 'لیست هدایا با موفقیت دریافت شد.',
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
     * Update a specific birthday gift
     *
     * @param UpdateBirthdayGiftRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateBirthdayGiftRequest $request, int $id)
    {
        try {
            $data = $this->birthdayGiftService->updateBirthdayGift($id, $request->validated());

            $payload = [
                'data' => $data,
                'message' => 'هدیه با موفقیت بروزرسانی شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'هدیه مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'BIRTHDAY_GIFT_NOT_FOUND',
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
     * Delete birthday gift
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        try {
            $data = $this->birthdayGiftService->delete($id);

            $payload = [
                'data' => $data,
                'message' => 'هدیه با موفقیت حذف شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'هدیه مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'BIRTHDAY_GIFT_NOT_FOUND',
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
