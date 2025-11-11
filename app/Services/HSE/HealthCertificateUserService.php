<?php

namespace App\Services\HSE;

use App\Models\User;
use App\Models\HSE\HealthCertificateUser;
use App\Repositories\HSE\HealthCertificateUserRepository;

class HealthCertificateUserService
{
    /**
     * @var HealthCertificateUserRepository
     */
    protected $healthCertificateUserRepository;

    /**
     * HealthCertificateUserService constructor
     *
     * @param HealthCertificateUserRepository $healthCertificateUserRepository
     */
    public function __construct(HealthCertificateUserRepository $healthCertificateUserRepository)
    {
        $this->healthCertificateUserRepository = $healthCertificateUserRepository;
    }

    public function createHealthCertificateUser($request)
    {
        $user = User::where('personnel_code', $request['code'])->first();
        if (!$user) {
            return ['reason' => 'کاربر یافت نشد', 'code' => $request['code']];
        }
        $request['user_id'] = $user->id;
        // Check for User existence with same Health Certificate ID and User ID
        if ($this->healthCertificateUserRepository->UserExist($request)) {
            return;
        }
        $healthCertificateUser = $this->healthCertificateUserRepository->create($request, null, null);
        return ['skipped' => false, 'data' => $this->formatHealthCertificateUserPayload($healthCertificateUser)];
    }

    /**
     * Format single Health Certificate User payload
     *
     * @param HealthCertificateUser $healthCertificateUser
     * @return array
     */
    protected function formatHealthCertificateUserPayload(HealthCertificateUser $healthCertificateUser): array
    {
        return [
            'id' => $healthCertificateUser->id,
            'healthCertificateId' => $healthCertificateUser->health_certificate_id,
            'userId' => $healthCertificateUser->user_id,
            'image' => $healthCertificateUser->image,
            'status' => $healthCertificateUser->status,
            'uploadedBy' => $healthCertificateUser->uploadedBy ? [
                'id' => $healthCertificateUser->uploadedBy->id,
                'fullName' => $healthCertificateUser->uploadedBy->first_name . ' ' . $healthCertificateUser->uploadedBy->last_name,
                'username' => $healthCertificateUser->uploadedBy->username,
            ] : null,
            'editedBy' => $healthCertificateUser->editedBy ? [
                'id' => $healthCertificateUser->editedBy->id,
                'fullName' => $healthCertificateUser->editedBy->first_name . ' ' . $healthCertificateUser->editedBy->last_name,
                'username' => $healthCertificateUser->editedBy->username,
            ] : null,
            'createdAt' => $healthCertificateUser->created_at,
            'updatedAt' => $healthCertificateUser->updated_at,
        ];
    }
}
