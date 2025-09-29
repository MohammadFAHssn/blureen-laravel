<?php

namespace App\Http\Controllers\Base;

use App\Services\Base\ApprovalFlowService;
use App\Http\Requests\Base\UpdateApprovalFlowsRequest;

class ApprovalFlowController
{
    protected $approvalFlowService;

    public function __construct()
    {
        $this->approvalFlowService = new ApprovalFlowService();
    }

    public function update(UpdateApprovalFlowsRequest $request)
    {
        return response()->json($this->approvalFlowService->update($request));
    }
}
