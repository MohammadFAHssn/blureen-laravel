<?php

namespace App\Jobs;

use App\Services\Api\RayvarzService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncWithRayvarz implements ShouldQueue
{
    use Queueable;

    protected $rayvarzService;

    protected $module;
    protected $modelName;
    protected $uniqueBy;

    /**
     * Create a new job instance.
     */
    public function __construct($module, $modelName, $uniqueBy)
    {
        $this->rayvarzService = new RayvarzService();

        $this->module = $module;
        $this->modelName = $modelName;
        $this->uniqueBy = $uniqueBy;

        $this->queue = 'rayvarz_sync';
    }


    public function handle(): void
    {
        if ($this->modelName === 'User') {
            $this->rayvarzService->syncUsers();
        } else {
            $this->rayvarzService->syncByFilters($this->module, $this->modelName, $this->uniqueBy, []);
        }
    }
}
