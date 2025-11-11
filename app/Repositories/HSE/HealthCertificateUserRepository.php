<?php

namespace App\Repositories\HSE;

use App\Models\HSE\HealthCertificateUser;
use Illuminate\Support\Facades\Auth;

class HealthCertificateUserRepository
{
    /**
     * create new Health Certificate User
     *
     * @param array|null $data
     * @param int|null $userId
     * @param int|null $healthCertificateId
     * @return \App\Models\HSE\HealthCertificateUser
     */
    public function create(?array $data = null, ?int $userId = null, ?int $healthCertificateId = null)
    {
        if (!empty($data)) {
            $data = array_merge($data, [
                'uploaded_by' => Auth::id(),
                'status' => 1,
            ]);
            $record = HealthCertificateUser::create($data);
            return $record->load('uploadedBy');
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

    /**
     * Check if there's a Health Certificate User with the same Health Certificate ID and User ID
     *
     * @param array $data
     * @return bool
     */
    public function UserExist(array $data)
    {
        return HealthCertificateUser::where('health_certificate_id', $data['health_certificate_id'])->where('user_id', $data['user_id'])->exists();
    }
}
