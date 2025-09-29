<?php

namespace App\Services\Base;

use App\Models\Base\ApprovalFlow;

class ApprovalFlowService
{
    public function update($request)
    {
        $approvalFlows = [];
        foreach ($request['approvalFlows'] as $approval) {

            $approvalDeleted = empty($approval['approver_user_id']) && empty($approval['approver_position_id']) && empty($approval['approver_center_id']);

            if (isset($approval['requester_user_id'])) {

                ApprovalFlow::where('requester_user_id', $approval['requester_user_id'])->delete();

                if ($approvalDeleted) {
                    continue;
                }

                $approvalFlows[] = $approval;
            } elseif (isset($approval['requester_position_id']) && isset($approval['requester_center_id'])) {

                ApprovalFlow::where('requester_position_id', $approval['requester_position_id'])
                    ->where('requester_center_id', $approval['requester_center_id'])
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
}
