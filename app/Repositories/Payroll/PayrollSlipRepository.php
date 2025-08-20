<?php

namespace App\Repositories\Payroll;

use App\Models\Payroll\PayrollSlip;
use Illuminate\Container\Attributes\Auth;

class PayrollSlipRepository
{
    public function getTheLastFewMonths($month, $year, $last)
    {
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
                'user:id,first_name,last_name,personnel_code',
                'payrollItems:payroll_slip_id,item_title,item_value',
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
