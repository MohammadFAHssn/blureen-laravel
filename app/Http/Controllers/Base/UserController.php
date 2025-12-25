<?php

namespace App\Http\Controllers\Base;

use App\Services\Base\UserService;
use App\Http\Requests\Base\RequestTypeIdRequest;
use App\Http\Requests\Base\ResetPasswordRequest;

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

    public function resetPassword(ResetPasswordRequest $request)
    {
        return response()->json(['data' => $this->userService->resetPassword($request)], 200);
    }

    public function getDetails()
    {
        return response()->json(['data' => $this->userService->getDetails()]);
    }
}
