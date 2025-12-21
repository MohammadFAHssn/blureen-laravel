<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contractor\CreateContractorRequest;
use App\Services\Contractor\ContractorService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class ContractorController
 *
 * Handles HTTP requests for Contractor management
 *
 * @package App\Http\Controllers\Contractor
 */
class ContractorController
{
    /**
     * @var ContractorService
     */
    protected $contractorService;

    /**
     * ContractorController constructor
     *
     * @param ContractorService $contractorService
     */
    public function __construct(ContractorService $contractorService)
    {
        $this->contractorService = $contractorService;
    }

    /**
     * Store a new contractor
     *
     * @param Request $request
     * @param CreateContractorRequest $request
     * @return JsonResponse
     */
    public function store(CreateContractorRequest $request)
    {
        try {
            $data = $this->contractorService->createContractor($request->validated());

            $payload = [
                'data' => $data,
                'message' => 'پیمانکار با موفقیت ایجاد شد.',
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
     * Get all contractors
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $data = $this->contractorService->getAllContractors();

            $payload = [
                'data' => $data,
                'message' => 'لیست پیمانکاران با موفقیت دریافت شد.',
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
     * Get all active contractors
     *
     * @return JsonResponse
     */
    public function getActives()
    {
        try {
            $data = $this->contractorService->getAllActiveContractors();

            $payload = [
                'data' => $data,
                'message' => 'لیست پیمانکاران با موفقیت دریافت شد.',
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
     * Change Status of a contractor
     *
     * @param int $id
     * @return JsonResponse
     */
    public function changeStatus(int $id)
    {
        try {
            $data = $this->contractorService->changeStatus($id);

            $payload = [
                'data' => $data,
                'message' => 'پیمانکار با موفقیت بروزرسانی شد.',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ModelNotFoundException $e) {
            $payload = [
                'message' => 'پیمانکار مورد نظر یافت نشد.',
                'status' => 404,
                'code' => 'CONTRACTOR_NOT_FOUND',
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
