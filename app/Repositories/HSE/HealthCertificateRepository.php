<?php

namespace App\Repositories\HSE;

use App\Models\HSE\HealthCertificate;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class HealthCertificateRepository
{
    /**
     * create new Health Certificate
     *
     * @param array $data
     * @return \App\Models\HSE\HealthCertificate
     */
    public function create(array $data)
    {
        $data = array_merge($data, [
            'uploaded_by' => Auth::id(),
            'status' => 1,
        ]);
        return HealthCertificate::create($data);
    }

    /**
     * Get all HealthCertificates
     *
     * @return array
     */
    public function getAll()
    {
        return HealthCertificate::get();
    }

    /**
     * Update HealthCertificate
     *
     * @param int $id
     * @param array $data
     * @return HealthCertificate
     */
    public function update(int $id, array $data)
    {
        $healthCertificate = $this->findById($id);
        $data['edited_by'] = Auth::id();
        $healthCertificate->update($data);
        return $healthCertificate;
    }

    /**
     * Delete HealthCertificate
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        $healthCertificate = $this->findById($id);
        return $healthCertificate->delete();
    }

    /**
     * Get HealthCertificate by ID
     *
     * @param int $id
     * @return HealthCertificate
     * @throws ModelNotFoundException
     */
    public function findById(int $id): HealthCertificate
    {
        return HealthCertificate::findOrFail($id);
    }

    /**
     * Show a specific healthCertificate’s users
     *
     * @return User
     */
    public function getHealthCertificateUsers()
    {
        return User::with('healthCertificate')->get();
    }

    /**
     * Check if there's a HealthCertificate File with the same month and year
     *
     * @param array $data
     * @return bool
     */
    public function healthCertificateExist(array $data)
    {
        return HealthCertificate::where('month', $data['month'])->where('year', $data['year'])->exists();
    }
}
