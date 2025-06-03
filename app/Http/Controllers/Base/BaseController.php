<?php

namespace App\Http\Controllers\Base;

use Illuminate\Http\Request;
use App\Services\Base\BaseService;

class BaseController
{
    public function manageResponse($serviceName, $action, $request)
    {
        return response()->json(['data' => app($serviceName)->$action($request)], 200);
    }

    public function get(Request $request)
    {
        return $this->manageResponse(BaseService::class, 'get', $request);
    }
}
