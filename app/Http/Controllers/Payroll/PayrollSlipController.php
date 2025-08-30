<?php

namespace App\Http\Controllers\Payroll;

use App\Services\Payroll\PayrollSlipService;
use App\Http\Requests\Payroll\GetTheLastFewMonthsPayrollSlipsRequest;

class PayrollSlipController
{

    protected $payrollSlipService;

    public function __construct()
    {
        $this->payrollSlipService = new PayrollSlipService();
    }

    public function getTheLastFewMonths(GetTheLastFewMonthsPayrollSlipsRequest $request)
    {
        return response()->json(['data' => $this->payrollSlipService->getTheLastFewMonths($request)], 200);
    }
}
