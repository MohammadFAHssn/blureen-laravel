<?php

namespace App\Repositories\HrRequest;

use App\Constants\AppConstants;
use App\Models\Base\ApprovalFlow;
use App\Models\HrRequest\HrRequest;
use App\Models\HrRequest\HrRequestApproval;

class HrRequestRepository {
    public function create(array $data)
    {
        $hrRequest = HrRequest::create([
            'user_id' => $data['user_id'],
            'request_type_id' => $data['request_type_id'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'status_id' => AppConstants::HR_REQUEST_PENDING_STATUS
        ]);

        if (!empty($data['details'])) {
            foreach ($data['details'] as $key => $value) {
                $hrRequest->details()->create(['key' => $key, 'value' => $value]);
            }
        }

        return $hrRequest;
    }

    public function getUserHourlyLeaveRequestsOfDay($userId, $date){
        return HrRequest::where([
            'request_type_id' => AppConstants::HR_REQUEST_TYPE_HOURLY_LEAVE,
            'user_id' => $userId,
            'start_date' => $date
        ])->whereNot('status_id',AppConstants::HR_REQUEST_REJECTED_STATUS)->get();
    }

    public function getApprovalRequestsByApprover($approverId)
    {
        /*return HrRequest::where([
            'status_id' => AppConstants::HR_REQUEST_PENDING_STATUS,
        ])->whereHas('approvals', function ($query) use )*/
    }
}
