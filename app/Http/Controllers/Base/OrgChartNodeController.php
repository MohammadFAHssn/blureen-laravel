<?php

namespace App\Http\Controllers\Base;

use App\Http\Requests\Base\UserIdRequest;
use App\Services\Base\OrgChartNodeService;
use App\Http\Requests\Base\UpdateOrgChartNodesRequest;

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

    public function getUserSubordinates(UserIdRequest $request)
    {
        return response()->json(['data' => $this->orgChartNodeService->getUserSubordinates($request->validated())]);
    }

    public function update(UpdateOrgChartNodesRequest $request)
    {
        return response()->json(['data' => $this->orgChartNodeService->update($request->validated())]);
    }
}
