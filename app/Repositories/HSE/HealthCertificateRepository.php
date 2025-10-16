<?php

namespace App\Repositories\HSE;

use App\Models\HSE\HealthCertificate;
use Illuminate\Support\Facades\Auth;

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
        // $path = request()->file('image')->store('birthdayGifts', 'public');
        // $data['image'] = $path;
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
        return HealthCertificate::with('users', 'uploadedBy', 'editedBy')->get();
    }

    /**
     * Get all active HealthCertificates
     *
     * @return array
     */
    public function getAllActive()
    {
        return HealthCertificate::with('users', 'createdBy', 'editedBy')->where('status', 1)->get();
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
        // if (request()->file('image')) {
        //     $path = request()->file('image')->store('healthCertificates', 'public');
        //     $data['image'] = $path;
        // }
        $data['edited_by'] = Auth::id();
        $healthCertificate = $this->findById($id);
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
