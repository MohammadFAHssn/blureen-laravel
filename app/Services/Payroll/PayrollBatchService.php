<?php
namespace App\Services\Payroll;

use App\Jobs\CreatePayrollBatchJob;
use Maatwebsite\Excel\Facades\Excel;

class PayrollBatchService
{
    public function create($request)
    {
        $month = $request['month'];
        $year = $request['year'];
        $file = $request['file'];

        $filename = $file->getClientOriginalName();

        $data = Excel::toArray([], $file); // all data in all sheets

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
