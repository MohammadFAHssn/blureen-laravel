<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseController;
use App\Services\Api\RayvarzService;

class RayvarzController extends BaseController
{
    public function sync(Request $request)
    {
        return $this->manageResponse(RayvarzService::class, 'sync', $request);
    }
}
