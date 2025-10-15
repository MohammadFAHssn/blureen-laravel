<?php

namespace App\Services\HrRequest;


use App\Constants\AppConstants;
use App\Exceptions\CustomException;
use App\Models\HrRequest\HrRequestApproval;
use App\Repositories\HrRequest\HrRequestApprovalRepository;
use App\Services\Base\ApprovalFlowService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;


class HrRequestApprovalService
{

    protected HrRequestApprovalRepository $hrRequestApprovalRepository;
    protected ApprovalFlowService $approvalFlowService;


    public function __construct(HrRequestApprovalRepository $hrRequestApprovalRepository,ApprovalFlowService $approvalFlowService)
    {
        $this->hrRequestApprovalRepository = $hrRequestApprovalRepository;
        $this->approvalFlowService = $approvalFlowService;
    }

    public function create(int $hrRequestId, Collection $userApprovalFlows): void
    {
        if ($userApprovalFlows->isEmpty())return;
        DB::transaction(function () use ($hrRequestId, $userApprovalFlows) {
            foreach ($userApprovalFlows as $flow) {
                HrRequestApproval::create([
                    'hr_request_id' => $hrRequestId,
                    'approval_flow_id' => $flow->id,
                    'status_id' => AppConstants::HR_REQUEST_PENDING_STATUS
                ]);
            }
        });
    }

}
