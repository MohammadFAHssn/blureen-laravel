<?php

namespace App\Repositories\HSE;

use App\Models\HSE\HealthCertificateUser;
use Illuminate\Support\Facades\Auth;

class HealthCertificateUserRepository
{
    /**
     * create new Health Certificate User
     *
     * @param int|null $userId
     * @param int|null $healthCertificateId
     * @param array|null $data
     * @return \App\Models\HSE\HealthCertificateUser
     */
    public function create(?int $userId, ?int $healthCertificateId, ?array $data = null)
    {
        if (!empty($data)) {
            return HealthCertificateUser::create($data);
        }
        $data = [
            'health_certificate_id' => $healthCertificateId,
            'uploaded_by' => Auth::id(),
            'status' => 1,
        ];
        if ($userId) {
            $data['user_id'] = $userId;
        }
        return HealthCertificateUser::create($data);
    }
}
