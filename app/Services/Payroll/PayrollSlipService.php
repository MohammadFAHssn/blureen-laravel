<?php

namespace App\Services\Payroll;

use App\Repositories\Payroll\PayrollSlipRepository;

class PayrollSlipService
{

    protected $payrollSlipRepository;

    public function __construct()
    {
        $this->payrollSlipRepository = new PayrollSlipRepository;
    }

    public function getTheLastFewMonths($request)
    {
        $month = $request->query('month', '');
        $year = $request->query('year', '');
        $last = $request->query('last', '');

        return $this->payrollSlipRepository->getTheLastFewMonths($month, $year, $last);
    }

    public function getReports($request)
    {
        return $this->payrollSlipRepository->getReports($request);
    }
}
