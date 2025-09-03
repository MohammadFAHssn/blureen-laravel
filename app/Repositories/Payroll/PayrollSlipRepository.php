<?php
namespace App\Repositories\Payroll;

use App\Exceptions\CustomException;
use App\Models\Payroll\PayrollBatch;
use App\Models\Payroll\PayrollSlip;

class PayrollSlipRepository
{
    public function getTheLastFewMonths($month, $year, $last)
    {
        $userId = auth()->user()->id;

        if (!$month && !$year) {
            $latestPayrollBatch = PayrollBatch::orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->first();

            $month = $latestPayrollBatch?->month;
            $year = $latestPayrollBatch?->year;
        }

        $isPayrollSlipExists = PayrollSlip::whereUserId($userId)->whereHas('payrollBatch', function ($query) use ($month, $year) {
            $query->where('month', $month)
                ->where('year', $year);
        })->exists();

        if (!$isPayrollSlipExists) {
            throw new CustomException('برای تاریخ انتخاب شده سندی یافت نشد.', 404);
        }

        $periods = $this->getPeriods($month, $year, $last);

        $payrollSlips = [];
        foreach ($periods as $period) {
            $payrollSlips[] = PayrollSlip::whereUserId($userId)
                ->whereHas('payrollBatch', function ($query) use ($period) {
                    $query->where('month', $period['month'])
                        ->where('year', $period['year']);
                })
                ->with([
                    'payrollItems:payroll_slip_id,item_title,item_value',
                    'payrollBatch:id,month,year',
                ])->first();
        }

        return $payrollSlips;
    }

    private function getPeriods($month, $year, $last)
    {
        $year = (int) $year;
        $periods = [];
        for ($i = 0; $i < $last; $i++) {

            if ($month - $i < 1) {
                $month = 12 + $i;
                $year = $year - 1;
            }

            $periods[] = [
                'month' => $month - $i,
                'year' => $year,
            ];
        }
        return $periods;
    }
}
