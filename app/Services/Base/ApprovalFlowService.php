<?php

namespace App\Services\Base;

use App\Models\Base\ApprovalFlow;
use App\Models\Base\UserProfile;
use Illuminate\Database\Eloquent\Collection;

class ApprovalFlowService
{
    public function update($request): void
    {
        $approvalFlows = [];
        foreach ($request['approvalFlows'] as $approval) {

            $approvalDeleted = empty($approval['approver_user_id']) && empty($approval['approver_position_id']) && empty($approval['approver_center_id']);

            if (isset($approval['requester_user_id'])) {

                ApprovalFlow::where('requester_user_id', $approval['requester_user_id'])
                    ->where('request_type_id', $approval['request_type_id'])
                    ->delete();

                if ($approvalDeleted) {
                    continue;
                }

                $approvalFlows[] = $approval;
            } elseif (isset($approval['requester_position_id']) && isset($approval['requester_center_id'])) {

                ApprovalFlow::where('requester_position_id', $approval['requester_position_id'])
                    ->where('requester_center_id', $approval['requester_center_id'])
                    ->where('request_type_id', $approval['request_type_id'])
                    ->delete();

                if ($approvalDeleted) {
                    continue;
                }

                $approvalFlows[] = $approval;
            }
        }

        foreach ($approvalFlows as $approval) {
            ApprovalFlow::create($approval);
        }
    }

    public function getUserApprovalFlow(int $userId, int $requestTypeId): Collection
    {
        $flows = ApprovalFlow::where([
            'requester_user_id' => $userId,
            'request_type_id'   => $requestTypeId,
        ])->get();

        if ($flows->isNotEmpty()) {
            return $flows;
        }

        $positionId = UserProfile::where('user_id', $userId)->value('job_position_id');

        if ($positionId) {
            return ApprovalFlow::where([
                'requester_position_id' => $positionId,
                'request_type_id'       => $requestTypeId,
            ])->get();
        }

        return new Collection();
    }
}
