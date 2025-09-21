<?php

namespace App\Repositories\Base;

use App\Models\Base\JobPosition;

class JobPositionRepository
{
    public function getApprovalFlowsAsRequester($requestTypeId)
    {
        return JobPosition::select('rayvarz_id', 'name')->with([
            'approvalFlowsAsRequester' => function ($query) use ($requestTypeId) {
                $query->where('request_type_id', $requestTypeId)->orderBy('priority');
            },
            'approvalFlowsAsRequester.approverUser:id,first_name,last_name,personnel_code',
            'approvalFlowsAsRequester.approverPosition:rayvarz_id,name',
        ])->get();
    }
}
