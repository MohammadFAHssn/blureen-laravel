<?php

namespace App\Http\Controllers\Birthday;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Birthday\BirthdayFileService;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Birthday\CreateBirthdayFileRequest;
use App\Http\Requests\Birthday\UpdateBirthdayFileRequest;

/**
 * Class BirthdayFileController
 *
 * Handles HTTP requests for BirthdayFile management
 *
 * @package App\Http\Controllers\Birthday
 */
class BirthdayFileController
{
    /**
     * @var BirthdayFileService
     */
    protected $birthdayFileService;

    /**
     * BirthdayFileController constructor
     *
     * @param BirthdayFileService $birthdayFileService
     */
    public function __construct(BirthdayFileService $birthdayFileService)
    {
        $this->birthdayFileService = $birthdayFileService;
    }

    /**
     * Store a new birthday file
     *
     * @param Request $request
     * @param CreateBirthdayFileRequest $request
     * @return JsonResponse
     */
    public function store(CreateBirthdayFileRequest $request)
    {
        try {
            $data = $this->birthdayFileService->createBirthdayFile($request);

            $payload = [
                'data' => $data,
                'message' => 'فایل هدیه با موفقیت آپلود شد.',
                'status' => 200,
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
     * Get all birthday files
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $data = $this->birthdayFileService->getAllBirthdayFiles();

            $payload = [
                'data' => $data,
                'message' => 'لیست فایل هدایا با موفقیت دریافت شد.',
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
     * Update a specific birthday file
     *
     * @param UpdateBirthdayFileRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateBirthdayFileRequest $request, int $id)
    {
        try {
            $data = $this->birthdayFileService->updateBirthdayFile($id, $request->validated());

            $payload = [
                'data' => $data,
                'message' => 'فایل با موفقیت بروزرسانی شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'فایل مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'BIRTHDAY_FILE_NOT_FOUND',
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
     * Delete birthday file
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        try {
            $data = $this->birthdayFileService->delete($id);

            $payload = [
                'data' => $data,
                'message' => 'فایل با موفقیت حذف شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'فایل مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'BIRTHDAY_FILE_NOT_FOUND',
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
