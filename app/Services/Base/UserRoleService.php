<?php

namespace App\Services\Base;

use App\Models\User;

class UserRoleService
{
    public function update($request)
    {
        $userId = $request['userId'];
        $roleIds = $request['roleIds'];

        $user = User::find($userId);

        $user->syncRoles($roleIds);
    }
}
