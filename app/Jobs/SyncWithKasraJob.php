<?php

namespace App\Jobs;

use App\Services\Api\KasraService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncWithKasraJob implements ShouldQueue
{
    use Queueable;

    protected $kasraService;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->kasraService = new KasraService();

        $this->queue = 'sync';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->kasraService->syncUsers();
    }
}
