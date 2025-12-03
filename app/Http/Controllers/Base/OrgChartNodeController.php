<?php

namespace App\Http\Controllers\Base;

use Illuminate\Http\Request;
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
}
