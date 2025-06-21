<?php

namespace App\Http\Controllers\Base;

use App\Services\Base\BaseService;
use Illuminate\Http\Request;

class BaseController
{
    protected $baseService;

    public function __construct()
    {
        $this->baseService = new BaseService();
    }

    public function get(Request $request)
    {
        return response()->json(['data' => $this->baseService->get($request)], 200);
    }
}
