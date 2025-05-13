<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BaseService;

class UserController extends BaseController
{
    public function get(Request $request)
    {
        return $this->manageResponse(BaseService::class, 'get', $request);
    }
}
