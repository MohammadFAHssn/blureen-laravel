<?php

namespace App\Repositories\HrRequest;



use App\Constants\AppConstants;
use App\Models\HrRequest\HrRequestDetail;

class HrRequestDetailRepository {
    public function getAllPendingLeaveRequestDuration($userId)
    {
        return HrRequestDetail::query()
            ->where('key', 'duration')
            ->whereHas('request', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->where('status_id', AppConstants::HR_REQUEST_PENDING_STATUS)
                    ->whereIn('request_type_id', [
                        AppConstants::HR_REQUEST_TYPES['DAILY_LEAVE'],
                        AppConstants::HR_REQUEST_TYPES['HOURLY_LEAVE'],
                    ]);
            })
            ->sum('value');
    }
}
