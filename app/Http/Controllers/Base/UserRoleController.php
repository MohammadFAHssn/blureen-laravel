<?php

namespace App\Http\Controllers\Base;

use App\Services\Base\UserRoleService;
use App\Http\Requests\Base\UpdateUserRolesRequest;

class UserRoleController
{
    protected $userRoleService;

    public function __construct()
    {
        $this->userRoleService = new UserRoleService();
    }

    public function update(UpdateUserRolesRequest $request)
    {
        return response()->json(['data' => $this->userRoleService->update($request)], 200);
    }
}
