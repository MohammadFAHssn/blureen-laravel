<?php

namespace App\Services\Base;

use App\Repositories\Base\JobPositionRepository;

class JobPositionService
{
    protected $jobPositionRepository;

    public function __construct()
    {
        $this->jobPositionRepository = new JobPositionRepository();
    }

    public function getApprovalFlowsAsRequester($request)
    {
        $requestTypeId = $request['requestTypeId'];

        return $this->jobPositionRepository->getApprovalFlowsAsRequester($requestTypeId);
    }
}
