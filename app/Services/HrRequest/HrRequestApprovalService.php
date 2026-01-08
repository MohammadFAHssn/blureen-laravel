<?php

namespace App\Services\HrRequest;


use App\Constants\AppConstants;
use App\Models\HrRequest\HrRequestApproval;
use App\Repositories\HrRequest\HrRequestApprovalRepository;
use App\Repositories\HrRequest\HrRequestRepository;
use App\Services\Api\KasraService;
use App\Services\Base\ApprovalFlowService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;


class HrRequestApprovalService
{

    protected HrRequestApprovalRepository $hrRequestApprovalRepository;
    protected ApprovalFlowService $approvalFlowService;
    protected HrRequestRepository $hrRequestRepository;
    protected KasraService $kasraService;


    public function __construct(
        HrRequestApprovalRepository $hrRequestApprovalRepository,
        ApprovalFlowService         $approvalFlowService,
        HrRequestRepository         $hrRequestRepository,
        KasraService                $kasraService,
    )
    {
        $this->hrRequestApprovalRepository = $hrRequestApprovalRepository;
        $this->approvalFlowService = $approvalFlowService;
        $this->hrRequestRepository = $hrRequestRepository;
        $this->kasraService = $kasraService;
    }

//todo: important change!!! replace approval_flow_id with approver_user_id
    public function create(int $hrRequestId, Collection $userApprovalFlows): void
    {
        if ($userApprovalFlows->isEmpty()) return;
        DB::transaction(function () use ($hrRequestId, $userApprovalFlows) {
            foreach ($userApprovalFlows as $flow) {
                HrRequestApproval::create([
                    'hr_request_id' => $hrRequestId,
                    'approver_user_id' => $flow->approver_user_id,
                    'priority' => $flow->priority,
                    'status_id' => AppConstants::HR_REQUEST_PENDING_STATUS
                ]);
            }
        });
    }


    /**
     * @throws Exception
     */
    public function getApprovalRequestsByApprover(): Collection
    {
        $userId = auth()->id();
        if (!$userId) throw new Exception('کاربر وارد نشده است', 401);
        return $this->hrRequestApprovalRepository->getApprovalRequestsByApprover($userId);
    }

    /**
     * @throws Exception
     */
    public function approveRequest($data): bool
    {
        foreach ($data['approvalRequestsIds'] as $approvalRequestId) {
            DB::transaction(function () use ($approvalRequestId, $data) {
                $hrRequestApproval = HrRequestApproval::find($approvalRequestId);
                if (!$hrRequestApproval) {
                    throw new Exception('درخواست تاییدیه یافت نشد', 404);
                }

                $hrRequestApproval->status_id = $data['approve']
                    ? AppConstants::HR_REQUEST_APPROVED_STATUS
                    : AppConstants::HR_REQUEST_REJECTED_STATUS;
                $hrRequestApproval->approved_at = now();
                $hrRequestApproval->description = $data['description'] ?? null;
                $hrRequestApproval->save();

                if (!$data['approve']) {
                    $this->hrRequestRepository->update([
                        'id' => $hrRequestApproval->hr_request_id,
                        'status_id' => AppConstants::HR_REQUEST_REJECTED_STATUS,
                    ]);
                    return;
                }

                if ($this->hrRequestApprovalRepository->checkAllRequestApprovalsIsApproved($hrRequestApproval->hr_request_id)) {
                    $this->hrRequestRepository->update([
                        'id' => $hrRequestApproval->hr_request_id,
                        'status_id' => AppConstants::HR_REQUEST_APPROVED_STATUS,
                    ]);
                    /*$kasraResponse = $this->kasraService->modifyCredit($hrRequestApproval->request);

                    if (!$kasraResponse['success']) {
                        throw new Exception($kasraResponse['message'], 422);
                    }*/
                }
            });
        }
        return true;
    }
}
