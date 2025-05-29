<?php

namespace App\Http\Controllers\Base;

use Exception;
use Illuminate\Http\Request;
use App\Services\Base\BaseService;

class BaseController
{
    public function manageResponse($serviceName, $action, $request)
    {
        try {
            return response()->json(['data' => app($serviceName)->$action($request)], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function get(Request $request)
    {
        return $this->manageResponse(BaseService::class, 'get', $request);
    }
}
