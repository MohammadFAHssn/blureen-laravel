<?php

namespace App\Repositories\Payroll;

use App\Exceptions\CustomException;
use App\Models\Payroll\PayrollSlip;

class PayrollSlipRepository
{
    public function getTheLastFewMonths($month, $year, $last)
    {
        $isPayrollSlipExists = PayrollSlip::whereHas('payrollBatch', function ($query) use ($month, $year) {
            $query->where('month', $month)
                ->where('year', $year);
        })->exists();

        if (!$isPayrollSlipExists) {
            throw new CustomException('برای تاریخ انتخاب شده سندی یافت نشد.', 404);
        }

        $periods = $this->getPeriods($month, $year, $last);

        $payrollSlips = PayrollSlip::whereUserId(auth()->user()->id)
            ->whereHas('payrollBatch', function ($query) use ($periods) {
                $query->where(function ($query) use ($periods) {
                    foreach ($periods as $period) {
                        $query->orWhere(function ($subQuery) use ($period) {
                            $subQuery->where('month', $period['month'])
                                ->where('year', $period['year']);
                        });
                    }
                });
            })
            ->with([
                'payrollItems:payroll_slip_id,item_title,item_value',
                'payrollBatch:id,month,year'
            ])->get();

        return $payrollSlips->sortByDesc(function ($item) {
            return $item->payrollBatch->year * 100 + $item->payrollBatch->month;
        })->values();
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
                'year' => $year
            ];
        }
        return $periods;
    }
}
