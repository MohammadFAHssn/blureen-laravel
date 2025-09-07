<?php
namespace App\Jobs;

use App\Models\User;
use App\Exceptions\CustomException;
use App\Models\Payroll\PayrollBatch;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class CreatePayrollBatchJob implements ShouldQueue
{
    use Queueable;

    protected $month;
    protected $year;
    protected $filename;
    protected $data;
    protected $uploadedBy;

    /**
     * Create a new job instance.
     */
    public function __construct($month, $year, $filename, $data, $uploadedBy)
    {
        $this->month = $month;
        $this->year = $year;
        $this->filename = $filename;
        $this->data = $data;
        $this->uploadedBy = $uploadedBy;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        info('Creating payroll batch...');

        $hasPayrollBatch = PayrollBatch::where('month', $this->month)
            ->where('year', $this->year)
            ->exists();

        if ($hasPayrollBatch) {
            throw new CustomException('قبلاً برای این دوره، فیش حقوقی بارگذاری شده است.', 400);
        }

        $payrollBatch = PayrollBatch::create([
            'month' => $this->month,
            'year' => $this->year,
            'uploaded_by' => $this->uploadedBy,
            'filename' => $this->filename,
        ]);

        try {

            $rows = $this->data[0]; // first sheet data

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
                    'id' => (int) $this->year . $this->month . $i,
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
