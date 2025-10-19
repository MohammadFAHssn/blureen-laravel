<?php

namespace App\Services\Base;

use Illuminate\Support\Facades\Hash;
use App\Repositories\Base\UserRepository;

class UserService
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function getApprovalFlowsAsRequester($request)
    {
        $requestTypeId = $request['requestTypeId'];

        return $this->userRepository->getApprovalFlowsAsRequester($requestTypeId);
    }

    public function resetPassword($request)
    {
        $user = auth()->user();

        $user->password = Hash::make($request['newPassword']);
        $user->save();

        return ['success' => true];
    }
}
