<?php

namespace App\Services\Base;

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
}
