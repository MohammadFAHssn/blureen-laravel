<?php

namespace App\Http\Controllers\Base;

use App\Services\Base\JobPositionService;
use App\Http\Requests\Base\RequestTypeIdRequest;

class JobPositionController
{
    protected $jobPositionService;

    public function __construct()
    {
        $this->jobPositionService = new JobPositionService();
    }

    public function getApprovalFlowsAsRequester(RequestTypeIdRequest $request)
    {
        return response()->json(['data' => $this->jobPositionService->getApprovalFlowsAsRequester($request)], 200);
    }
}
