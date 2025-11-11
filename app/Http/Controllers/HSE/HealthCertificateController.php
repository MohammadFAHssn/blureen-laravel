<?php

namespace App\Http\Controllers\HSE;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\HSE\HealthCertificateService;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\HSE\CreateHealthCertificateRequest;
use App\Http\Requests\HSE\UpdateHealthCertificateRequest;

/**
 * Class HealthCertificateController
 *
 * Handles HTTP requests for HealthCertificate management
 *
 * @package App\Http\Controllers\HSE
 */
class HealthCertificateController
{
    /**
     * @var HealthCertificateService
     */
    protected $healthCertificateService;

    /**
     * HealthCertificateController constructor
     *
     * @param HealthCertificateService $healthCertificateService
     */
    public function __construct(HealthCertificateService $healthCertificateService)
    {
        $this->healthCertificateService = $healthCertificateService;
    }

    /**
     * Store a HealthCertificate
     *
     * @param Request $request
     * @param CreateHealthCertificateRequest $request
     * @return JsonResponse
     */
    public function store(CreateHealthCertificateRequest $request)
    {
        try {
            $data = $this->healthCertificateService->createHealthCertificate($request);

            $payload = [
                'data' => $data,
                'message' => 'دوره شناسنامه با موفقیت ایجاد شد.',
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
     * Get all HealthCertificate Periods
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $data = $this->healthCertificateService->getAllHealthCertificates();

            $payload = [
                'data' => $data,
                'message' => 'لیست دوره‌های شناسنامه سلامت با موفقیت دریافت شد.',
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
     * Update a HealthCertificate
     *
     * @param UpdateHealthCertificateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateHealthCertificateRequest $request, int $id)
    {
        try {
            $data = $this->healthCertificateService->updateHealthCertificate($id, $request->validated());

            $payload = [
                'data' => $data,
                'message' => 'شناسنامه سلامت با موفقیت بروزرسانی شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'شناسنامه سلامت مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'HEALTH_CERTIFICATE_NOT_FOUND',
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
     * Delete HealthCertificate
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        try {
            $data = $this->healthCertificateService->delete($id);

            $payload = [
                'data' => $data,
                'message' => 'شناسنامه سلامت با موفقیت حذف شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'شناسنامه سلامت مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'HEALTH_CERTIFICATE_NOT_FOUND',
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
     * Show a specific HealthCertificate
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        try {
            $data = $this->healthCertificateService->getHealthCertificate($id);

            $payload = [
                'data' => $data,
                'message' => 'شناسنامه سلامت با موفقیت دریافت شد.',
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
