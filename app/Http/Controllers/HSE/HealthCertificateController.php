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
                'message' => 'گروه با موفقیت ایجاد شد.',
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
