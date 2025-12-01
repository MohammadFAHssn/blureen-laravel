<?php

namespace App\Services\Base;

class RequestApprovalRuleService
{
    protected $orgChartNodeService;

    public function __construct()
    {
        $this->orgChartNodeService = new OrgChartNodeService();
    }

    public function getApprovalFlowForRequest($requestTypeId, $userId)
    {
    }
}
