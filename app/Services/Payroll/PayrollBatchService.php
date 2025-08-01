<?php

namespace App\Services\Payroll;

use App\Models\User;
use App\Exceptions\CustomException;
use App\Models\Payroll\PayrollItem;
use App\Models\Payroll\PayrollSlip;
use App\Models\Payroll\PayrollBatch;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;

class PayrollBatchService
{
    public function create($request)
    {
        $month = $request['month'];
        $year = $request['year'];
        $file = $request['file'];

        $filename = $file->getClientOriginalName();

        $userId = auth()->user()->id;

        try {
            $payrollBatch = PayrollBatch::create([
                'month' => $month,
                'year' => $year,
                'uploaded_by' => $userId,
                'filename' => $filename,
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                throw new CustomException('قبلاً برای این دوره، فیش حقوقی بارگذاری شده است.', 400);
            }
            throw $e;
        }

        $data = Excel::toArray([], $file); // all data in all sheets
        $rows = $data[0]; // first sheet data

        $headers = $rows[0];

        $personnelCodeIndex = array_search('پرسنلی', $headers);

        if (!$personnelCodeIndex) {
            throw new CustomException('ستون "پرسنلی" در فایل اکسل بارگذاری شده وجود ندارد.', 400);
        }

        for ($i = 1; $i < count($rows); $i++) {

            $userId = User::wherePersonnelCode($rows[$i][$personnelCodeIndex])->value('id');

            $payrollSlip = PayrollSlip::create([
                'user_id' => $userId,
                'batch_id' => $payrollBatch->id
            ]);

            foreach ($headers as $index => $header) {
                PayrollItem::create([
                    'payroll_slip_id' => $payrollSlip->id,
                    'item_title' => $header,
                    'item_value' => $rows[$i][$index]
                ]);
            }
        }
    }
}
