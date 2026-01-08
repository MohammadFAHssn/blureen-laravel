<?php

namespace App\Http\Controllers\Payroll;

use App\Services\Payroll\PayrollSlipService;
use App\Http\Requests\Payroll\PrintPayrollSlipRequest;
use App\Http\Requests\Payroll\GetTheLastFewMonthsPayrollSlipsRequest;

use App\Http\Requests\Payroll\GetPayrollSlipReportsRequest;

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

    public function getReports(GetPayrollSlipReportsRequest $request)
    {
        return $this->payrollSlipService->getReports($request);
    }

    public function print(PrintPayrollSlipRequest $request)
    {
        return $this->payrollSlipService->print($request);
    }
}
