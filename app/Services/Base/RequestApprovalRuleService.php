<?php
namespace App\Services\Base;

use App\Exceptions\CustomException;
use App\Models\Base\RequestApprovalRule;

class RequestApprovalRuleService
{
    protected $orgChartNodeService;

    public function __construct()
    {
        $this->orgChartNodeService = new OrgChartNodeService;
    }

    public function getApprovalFlowForRequest($requestTypeId, $userId)
    {
        $userOrgPositions = $this->orgChartNodeService->getUserOrgPositions($userId);

        if ($userOrgPositions->isEmpty()) {
            throw new CustomException('کاربر مورد نظر جایگاهی در چارت سازمانی ندارد.', 404);
        }

        if ($userOrgPositions->count() > 1) {
            throw new CustomException('کاربر مورد نظر دارای دو جایگاه در چارت سازمانی می‌باشد، لطفاً از استثنائات استفاده کنید.', 409);
        }

        $userOrgPosition = $userOrgPositions->first();

        return RequestApprovalRule::with([
            'approverOrgChartNode.user:id,first_name,last_name,personnel_code',
            'approverOrgChartNode.orgPosition',
            'approverOrgChartNode.orgUnit',
        ])
            ->where('request_type_id', $requestTypeId)
            ->where('requester_org_position_id', $userOrgPosition->id)
            ->orderBy('priority', 'asc')
            ->get()
            ->map(function ($rule) use ($userId) {
                if ($rule->approver_org_position_id) {
                    $userSupervisor = $this->orgChartNodeService->getUserSupervisor($userId, $rule->approver_org_position_id)[0] ?? null;
                    if ($userSupervisor) {
                        return [...$userSupervisor, 'priority' => $rule->priority];
                    } else {
                        return null;
                    }
                } elseif ($rule->approverOrgChartNode) {
                    return [
                        ...$rule->approverOrgChartNode->only(['user', 'orgPosition', 'orgUnit']),
                        'priority' => $rule->priority,
                    ];
                }
            })
            ->filter()
            ->values();
    }
}
