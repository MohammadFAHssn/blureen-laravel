<?php

namespace App\Http\Controllers\Payroll;

use App\Services\Payroll\PayrollBatchService;
use App\Http\Requests\Payroll\CreatePayrollBatchRequest;

class PayrollBatchController
{
    protected $payrollBatchService;

    public function __construct()
    {
        $this->payrollBatchService = new PayrollBatchService();
    }

    public function create(CreatePayrollBatchRequest $request)
    {
        return response()->json(['data' => $this->payrollBatchService->create($request)], 200);
    }
}
