<?php

namespace App\Http\Controllers\Base;

use App\Services\Base\JobPositionService;

class JobPositionController
{
    protected $jobPositionService;

    public function __construct()
    {
        $this->jobPositionService = new JobPositionService();
    }
}
