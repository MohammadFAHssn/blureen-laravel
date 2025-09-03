<?php
namespace App\Jobs;

use App\Exceptions\CustomException;
use App\Models\Payroll\PayrollBatch;
use App\Models\Payroll\PayrollItem;
use App\Models\Payroll\PayrollSlip;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Queue\Queueable;

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

        try {
            $payrollBatch = PayrollBatch::create([
                'month' => $this->month,
                'year' => $this->year,
                'uploaded_by' => $this->uploadedBy,
                'filename' => $this->filename,
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                throw new CustomException('قبلاً برای این دوره، فیش حقوقی بارگذاری شده است.', 400);
            }
            throw $e;
        }

        try {

            $rows = $this->data[0]; // first sheet data

            $headers = $rows[0];

            $personnelCodeIndex = array_search('پرسنلی', $headers);

            if ($personnelCodeIndex === false) {
                $payrollBatch->delete();
                throw new CustomException('ستون "پرسنلی" در فایل اکسل بارگذاری شده وجود ندارد.', 400);
            }

            for ($i = 1; $i < count($rows); $i++) {

                if (empty($rows[$i][$personnelCodeIndex])) {
                    break;
                }

                $userId = User::wherePersonnelCode($rows[$i][$personnelCodeIndex])->value('id');

                $payrollSlip = PayrollSlip::create([
                    'user_id' => $userId,
                    'batch_id' => $payrollBatch->id,
                ]);

                $payrollSlipItems = [];
                foreach ($headers as $index => $header) {
                    if (!empty($rows[$i][$index])) {
                        $payrollSlipItems[] = [
                            'payroll_slip_id' => $payrollSlip->id,
                            'item_title' => $header,
                            'item_value' => $rows[$i][$index],
                        ];
                    }
                }

                PayrollItem::insert($payrollSlipItems);
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
