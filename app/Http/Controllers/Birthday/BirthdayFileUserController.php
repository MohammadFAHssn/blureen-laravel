<?php

namespace App\Http\Controllers\Birthday;

use App\Http\Requests\Birthday\CreateBirthdayFileUserRequest;
use App\Services\Birthday\BirthdayFileUserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class BirthdayFileUserController
 *
 * Handles HTTP requests for BirthdayFileUser management
 *
 * @package App\Http\Controllers\Birthday
 */
class BirthdayFileUserController
{
    /**
     * @var BirthdayFileUserService
     */
    protected $birthdayFileUserService;

    /**
     * BirthdayFileUserController constructor
     *
     * @param BirthdayFileUserService $birthdayFileUserService
     */
    public function __construct(BirthdayFileUserService $birthdayFileUserService)
    {
        $this->birthdayFileUserService = $birthdayFileUserService;
    }

    /**
     * Store a new BirthdayFileUser
     *
     * @param Request $request
     * @param CreateBirthdayFileUserRequest $request
     * @return JsonResponse
     */
    public function store(CreateBirthdayFileUserRequest $request)
    {
        try {
            $results = [];
            $skipped = [];

            foreach ($request->codes as $code) {
                $res = $this->birthdayFileUserService->createBirthdayFileUser([
                    'code' => $code,
                    'birthday_file_id' => $request->birthday_file_id,
                ]);

                // user not found (skipped)
                if (isset($res['reason'])) {
                    $skipped[] = $res;
                    continue;
                }

                // user exists (already in file) → ignore silently
                if (!$res) {
                    continue;
                }

                // newly created
                $results[] = $res;
            }

            $payload = [
                'created' => $results,
                'skipped' => $skipped,
                'message' => 'لیست کاربران فایل هدیه، با موفقیت بروزرسانی شد',
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
     * Delete one or multiple BirthdayFileUser
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        try {
            $results = [];

            foreach ($request->codes as $code) {
                $res = $this->birthdayFileUserService->delete($code);
                $results[] = $res;
            }

            $payload = [
                'data' => $results,
                'message' => 'لیست کاربران فایل هدیه، با موفقیت بروزرسانی شد',
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
     * Change Status of one or more BirthdayFileUser
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changeStatus(Request $request)
    {
        try {
            $results = [];

            foreach ($request->codes as $code) {
                $res = $this->birthdayFileUserService->changeStatus($code);
                $results[] = $res;
            }

            $payload = [
                'data' => $results,
                'message' => 'لیست کاربران فایل هدیه، با موفقیت بروزرسانی شد',
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
}
