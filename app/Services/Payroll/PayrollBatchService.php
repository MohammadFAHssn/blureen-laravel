<?php

namespace App\Services\Payroll;

class PayrollBatchService
{
    public function create($request)
    {
        $file = $request['file'];
        $month = $request['month'];
        $year = $request['year'];

        $filename = $file->getClientOriginalName();
    }
}
