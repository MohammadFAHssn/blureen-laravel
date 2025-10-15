<?php
namespace App\Http\Controllers\HrRequest;
use App\Exceptions\CustomException;
use App\Http\Requests\HrRequest\CreateHrRequest;
use App\Services\HrRequest\HrRequestService;
use Illuminate\Http\JsonResponse;

class HrRequestController
{
    protected HrRequestService $hrRequestService;

    public function __construct(HrRequestService $hrRequestService)
    {
        $this->hrRequestService = $hrRequestService;
    }

    /**
     * @throws CustomException
     */
    public function create(CreateHrRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->hrRequestService->create($request->validated())
        ]);
    }

    public function getApprovalRequestsByApprover(): JsonResponse
    {
        return response()->json([
            'data' => $this->hrRequestService->getApprovalRequestsByApprover()
        ]);
    }
}
