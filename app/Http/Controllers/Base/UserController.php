<?php

namespace App\Http\Controllers\Base;

use Illuminate\Http\Request;
use App\Services\Base\BaseService;
use App\Http\Controllers\Base\BaseController;

class UserController extends BaseController
{
    public function get(Request $request)
    {
        return $this->manageResponse(BaseService::class, 'get', $request);
    }
}
