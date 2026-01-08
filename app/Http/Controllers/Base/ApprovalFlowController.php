<?php

namespace App\Http\Controllers\Base;

use App\Exceptions\CustomException;
use App\Http\Requests\Base\GetByUserIdRequest;
use App\Services\Base\ApprovalFlowService;
use App\Http\Requests\Base\UpdateApprovalFlowsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalFlowController
{
    protected ApprovalFlowService $approvalFlowService;

    public function __construct(ApprovalFlowService $approvalFlowService)
    {
        $this->approvalFlowService = $approvalFlowService;
    }

    public function update(UpdateApprovalFlowsRequest $request): JsonResponse
    {
        return response()->json($this->approvalFlowService->update($request));
    }

    /**
     * @throws CustomException
     */
    public function getSubUsers(Request $request){
        return response()->json(['data' => $this->approvalFlowService->getRequestersForCurrentApprover($request->toArray())]);
    }
}
