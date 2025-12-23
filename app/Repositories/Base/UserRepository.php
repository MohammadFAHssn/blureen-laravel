<?php

namespace App\Repositories\Base;
use App\Models\User;

class UserRepository
{
    public function getApprovalFlowsAsRequester($requestTypeId)
    {
        return null;
    }

    /**
     * Get user by ID
     *
     * @param int $id
     * @return User
     * @throws ModelNotFoundException
     */
    public function findById(int $id): User
    {
        return User::findOrFail($id);
    }
}
