<?php

namespace App\Repositories\Base;

use App\Models\User;

class UserRepository
{
    public function getApprovalFlowsAsRequester($requestTypeId)
    {
        $user = auth()->user();
        $allowedCostCenters = $user->costCentersAsLiaison();

        return User::select('id', 'first_name', 'last_name', 'personnel_code', 'active')
            ->whereHas('profile.costCenter', function ($query) use ($allowedCostCenters) {
                $query->whereIn('rayvarz_id', $allowedCostCenters->pluck('cost_center_rayvarz_id'));
            })
            ->with([
                'profile:user_id,workplace_id,work_area_id,cost_center_id,job_position_id',
                'profile.workplace:rayvarz_id,name',
                'profile.workArea:rayvarz_id,name',
                'profile.costCenter:rayvarz_id,name',
                'profile.jobPosition:rayvarz_id,name',
                'approvalFlowsAsRequester' => function ($query) use ($requestTypeId) {
                    $query->where('request_type_id', $requestTypeId)->orderBy('priority');
                },
                'approvalFlowsAsRequester.approverUser:id,first_name,last_name,personnel_code',
                'approvalFlowsAsRequester.approverPosition:rayvarz_id,name',
            ])->get();
    }
}
