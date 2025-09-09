<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Requests\Base\DeleteRequest;
use App\Services\Base\BaseService;
use App\Services\Payroll\PayrollBatchService;
use App\Http\Requests\Payroll\CreatePayrollBatchRequest;


class PayrollBatchController
{
    protected $payrollBatchService;
    protected $baseService;

    public function __construct()
    {
        $this->payrollBatchService = new PayrollBatchService();
        $this->baseService = new BaseService();
    }

    public function create(CreatePayrollBatchRequest $request)
    {
        return response()->json(['data' => $this->payrollBatchService->create($request)], 200);
    }

    public function delete(DeleteRequest $request)
    {
        return response()->json(['data' => $this->baseService->delete($request)], 200);
    }
}
