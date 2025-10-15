<?php
namespace App\Repositories\Payroll;

use App\Exports\PayrollSlipExport;
use App\Exceptions\CustomException;
use App\Models\Payroll\PayrollSlip;
use App\Models\Payroll\PayrollBatch;
use Maatwebsite\Excel\Facades\Excel;

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

        if ($last == 2) {
            PayrollSlip::whereUserId($userId)
                ->whereHas('payrollBatch', function ($query) use ($month, $year) {
                    $query->where('month', $month)
                        ->where('year', $year);
                })->update(['is_viewed' => true]);
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

    public function getReports($request)
    {
        $month = $request['month'];
        $year = $request['year'];

        $unviewedPayrollSlipUsers = PayrollSlip::where('is_viewed', false)
            ->whereHas('payrollBatch', function ($query) use ($month, $year) {
                $query->where('month', $month)
                    ->where('year', $year);
            })->with([
                    'user.profile.workplace',
                    'user.profile.workArea',
                    'user.profile.costCenter',
                    'user.profile.jobPosition',
                ])->get()->map(function ($slip) {
                    $profile = $slip->user->profile;
                    return [
                        'personnel_code' => $slip->user->personnel_code,
                        'first_name' => $slip->user->first_name,
                        'last_name' => $slip->user->last_name,
                        'workplace' => $profile?->workplace?->name,
                        'work_area' => $profile?->workArea?->name,
                        'cost_center' => $profile?->costCenter?->name,
                        'job_position' => $profile?->jobPosition?->name,
                    ];
                })->toArray();

        $headings = ['کد پرسنلی', 'نام', 'نام خانوادگی', 'محل کار', 'منطقه کاری', 'مرکز هزینه', 'سمت'];

        return Excel::download(new PayrollSlipExport($unviewedPayrollSlipUsers, $headings), 'گزارش فیش حقوقی.xlsx');
    }
}
