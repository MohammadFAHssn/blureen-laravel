<?php

namespace App\Http\Controllers\Base;

use App\Http\Requests\Base\UserIdRequest;
use App\Services\Base\OrgChartNodeService;

class OrgChartNodeController
{
    protected $orgChartNodeService;

    public function __construct()
    {
        $this->orgChartNodeService = new OrgChartNodeService();
    }

    public function get()
    {
        return response()->json(['data' => $this->orgChartNodeService->get()], 200);
    }

    public function getUserOrgChartNodes(UserIdRequest $request)
    {
        return response()->json(['data' => $this->orgChartNodeService->getUserOrgChartNodes($request->validated())]);
    }

    public function getUserChild(UserIdRequest $request)
    {
        return response()->json(['data' => $this->orgChartNodeService->getUserChild($request->validated())]);
    }

    public function getUserAndChild(UserIdRequest $request)
    {
        return response()->json(['data' => $this->orgChartNodeService->getUserAndChild($request->validated())]);
    }
}
