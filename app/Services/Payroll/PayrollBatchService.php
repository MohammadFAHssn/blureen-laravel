<?php
namespace App\Services\Payroll;

use App\Exceptions\CustomException;
use App\Models\Payroll\PayrollBatch;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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

        info('Creating payroll batch...');

        $hasPayrollBatch = PayrollBatch::where('month', $month)
            ->where('year', $year)
            ->exists();

        if ($hasPayrollBatch) {
            throw new CustomException('قبلاً برای این دوره، فیش حقوقی بارگذاری شده است.', 400);
        }

        $payrollBatch = PayrollBatch::create([
            'month' => $month,
            'year' => $year,
            'uploaded_by' => $uploadedBy,
            'filename' => $filename,
        ]);

        try {

            $rows = $data[0]; // first sheet data

            $headers = $rows[0];

            $personnelCodeIndex = array_search('پرسنلی', $headers, true);

            if ($personnelCodeIndex === false) {
                $payrollBatch->delete();
                throw new CustomException('ستون "پرسنلی" در فایل اکسل بارگذاری شده وجود ندارد.', 400);
            }

            $personnelCodes = [];
            for ($i = 1; $i < count($rows); $i++) {
                $code = $rows[$i][$personnelCodeIndex] ?? null;
                if ($code === null || $code === '') {
                    continue;
                }
                $personnelCodes[] = $code;
            }

            $usersMap = User::whereIn('personnel_code', $personnelCodes)
                ->pluck('id', 'personnel_code');

            $missing = array_diff($personnelCodes, array_keys($usersMap->toArray()));
            if (!empty($missing)) {
                throw new CustomException('کدهای پرسنلی نامعتبر: ' . implode(', ', $missing), 404);
            }

            $payrollSlips = [];
            $payrollItems = [];
            for ($i = 1; $i < count($rows); $i++) {

                $personnelCode = $rows[$i][$personnelCodeIndex] ?? null;
                if ($personnelCode === null || $personnelCode === '') {
                    continue;
                }

                $userId = $usersMap[$personnelCode];

                $payrollSlip = [
                    'id' => (int) $year . $month . $i,
                    'user_id' => $userId,
                    'batch_id' => $payrollBatch->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $payrollSlips[] = $payrollSlip;

                foreach ($headers as $index => $header) {
                    if (!empty($rows[$i][$index])) {
                        $payrollItems[] = [
                            'payroll_slip_id' => $payrollSlip['id'],
                            'item_title' => $header,
                            'item_value' => Crypt::encryptString((string) $rows[$i][$index]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
            foreach (array_chunk($payrollSlips, 500) as $chunk) {
                DB::table('payroll_slips')->insert($chunk);
            }

            foreach (array_chunk($payrollItems, 5000) as $chunk) {
                DB::table('payroll_items')->insert($chunk);
            }
        } catch (\Exception $e) {
            info('Error processing payroll batch', [
                'error' => $e->getMessage(),
            ]);
            $payrollBatch->delete();
            throw $e;
        }

        info('Payroll batch created successfully.');
    }
}
