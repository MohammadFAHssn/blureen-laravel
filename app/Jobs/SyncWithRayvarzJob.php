<?php

namespace App\Jobs;

use App\Services\Api\RayvarzService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use App\Enums\Rayvarz as RayvarzEnums;


class SyncWithRayvarzJob implements ShouldQueue
{
    use Queueable;

    protected $rayvarzService;

    protected $module;
    protected $modelName;
    protected $uniqueBy;

    /**
     * Create a new job instance.
     */
    public function __construct($module, $modelName, $uniqueBy = '')
    {
        $this->rayvarzService = new RayvarzService();

        $this->module = $module;
        $this->modelName = $modelName;
        $this->uniqueBy = $uniqueBy;

        $this->queue = 'sync';
    }


    public function handle(): void
    {
        if ($this->modelName === 'User') {
            $this->rayvarzService->syncUsers();
        } elseif (in_array(Str::snake(Str::pluralStudly($this->modelName)), RayvarzEnums::REPORTS)) {
            $this->rayvarzService->syncReports(array_search(Str::snake(Str::pluralStudly($this->modelName)), RayvarzEnums::REPORTS, true));
        } else {
            $this->rayvarzService->syncByFilters($this->module, $this->modelName, $this->uniqueBy, []);
        }
    }
}
