<!-- TODO remove this job -->

<?php
namespace App\Jobs;


use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;


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


    }
}
