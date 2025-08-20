<?php

namespace App\Services\Payroll;

class PayrollSlipService
{
    public function getTheLastFewMonths($request)
    {
        $month = $request->query('month', '');
        $year = $request->query('year', '');
        $last = $request->query('last', '');

        return [
            'month' => $month,
            'year' => $year,
            'last' => $last,
        ];
    }
}
