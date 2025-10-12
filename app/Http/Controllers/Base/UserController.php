<?php

namespace App\Http\Controllers\Base;

use App\Services\Base\UserService;
use App\Http\Requests\Base\RequestTypeIdRequest;

class UserController
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function getApprovalFlowsAsRequester(RequestTypeIdRequest $request)
    {
        return response()->json(['data' => $this->userService->getApprovalFlowsAsRequester($request)], 200);
    }
}
