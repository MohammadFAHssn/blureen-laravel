<?php

namespace App\Http\Controllers\Payroll;

use Illuminate\Http\Request;
use App\Services\Base\BaseService;

class PayrollSlipController
{

    protected $baseService;

    public function __construct()
    {
        $this->baseService = new BaseService();
    }

    public function get(Request $request)
    {
        $route = $request->route();

        $route->setParameter('module', 'payroll');
        $route->setParameter('model_name', 'payroll-slip');


        return response()->json(['data' => $this->baseService->get($request)], 200);

    }
}
