<?php

namespace App\Http\Controllers;

use Exception;

class BaseController
{
    public function manageResponse($serviceName, $action, $request)
    {
        try {
            return response()->json(['data' => $serviceName::$action($request)], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
