<?php

namespace App\Repositories\HrRequest;

use App\Constants\AppConstants;
use App\Models\Base\ApprovalFlow;
use App\Models\HrRequest\HrRequest;
use App\Models\HrRequest\HrRequestApproval;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class HrRequestApprovalRepository {

    public function getApprovalRequestsByApprover($approverUserId): Collection
    {
        return HrRequestApproval::query()
            ->where('approver_user_id', $approverUserId)
            ->where('status_id', AppConstants::HR_REQUEST_PENDING_STATUS)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('hr_request_approvals as b')
                    ->whereColumn('b.hr_request_id', 'hr_request_approvals.hr_request_id')
                    ->whereColumn('b.priority', '<', 'hr_request_approvals.priority')
                    ->where('b.status_id', '<>', AppConstants::HR_REQUEST_APPROVED_STATUS);
            })
            ->with('request.user','request.type','request.details')
            ->get();
    }



    public function checkAllRequestApprovalsIsApproved($hrRequestId)
    {
        return HrRequestApproval::where([
            'hr_request_id' => $hrRequestId,
            'status_id' => AppConstants::HR_REQUEST_PENDING_STATUS
        ])->doesntExist();
    }

}
