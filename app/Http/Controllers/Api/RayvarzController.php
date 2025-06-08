<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\BaseController;
use App\Services\Api\RayvarzService;
use Illuminate\Http\Request;

class RayvarzController extends BaseController
{
    public function sync(Request $request)
    {
        return $this->manageResponse(RayvarzService::class, 'sync', $request);
    }
}
