<?php
namespace App\Services\Payroll;

use App\Jobs\CreatePayrollBatchJob;
use Maatwebsite\Excel\Facades\Excel;
use App\Exceptions\CustomException;

class PayrollBatchService
{
    public function create($request)
    {
        $month = $request['month'];
        $year = $request['year'];
        $file = $request['file'];

        try {
            $filename = $file->getClientOriginalName();
            $data = Excel::toArray([], $file); // all data in all sheets
        } catch (\Exception $e) {
            info('Error reading payroll file: ' . $e->getMessage());
            throw new CustomException('خطا در خواندن فایل بارگذاری شده.', 422);
        }

        $uploadedBy = auth()->user()->id;

        info('Dispatching CreatePayrollBatchJob...');

        CreatePayrollBatchJob::dispatch(
            $month,
            $year,
            $filename,
            $data,
            $uploadedBy
        );
    }
}
