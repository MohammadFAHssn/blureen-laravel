<?php

namespace App\Http\Controllers\Payroll;

use App\Services\Payroll\PayrollSlipService;
use Illuminate\Http\Request;

class PayrollSlipController
{

    protected $payrollSlipService;

    public function __construct()
    {
        $this->payrollSlipService = new PayrollSlipService();
    }

    public function getTheLastFewMonths(Request $request)
    {
        return response()->json(['data' => $this->payrollSlipService->getTheLastFewMonths($request)], 200);
    }
}
