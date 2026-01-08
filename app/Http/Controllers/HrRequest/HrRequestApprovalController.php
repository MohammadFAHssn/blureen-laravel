<?php
namespace App\Http\Controllers\HrRequest;
use App\Exceptions\CustomException;
use App\Http\Requests\HrRequest\CreateHrRequest;
use App\Services\HrRequest\HrRequestApprovalService;
use App\Services\HrRequest\HrRequestService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HrRequestApprovalController
{
    protected HrRequestApprovalService $hrRequestApprovalService;

    public function __construct(HrRequestApprovalService $hrRequestApprovalService)
    {
        $this->hrRequestApprovalService = $hrRequestApprovalService;
    }

    /**
     * @throws Exception
     */
    public function getApprovalRequestsByApprover(): JsonResponse
    {
        return response()->json([
            'data' => $this->hrRequestApprovalService->getApprovalRequestsByApprover()
        ]);
    }

    /**
     * @throws Exception
     */
    public function approveRequest(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->hrRequestApprovalService->approveRequest($request->toArray())
        ]);
    }

}
