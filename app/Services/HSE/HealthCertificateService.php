<?php

namespace App\Services\HSE;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\HSE\HealthCertificate;
use Illuminate\Validation\ValidationException;
use App\Repositories\HSE\HealthCertificateRepository;

class HealthCertificateService
{
    /**
     * @var HealthCertificateRepository
     */
    protected $healthCertificateRepository;

    /**
     * HealthCertificateService constructor
     *
     * @param HealthCertificateRepository $healthCertificateRepository
     * @param HealthCertificateUserRepository $healthCertificateUserRepository
     */
    public function __construct(HealthCertificateRepository $healthCertificateRepository)
    {
        $this->healthCertificateRepository = $healthCertificateRepository;
    }

    public function createHealthCertificate($request)
    {
        $validatedRequest = $request->validated();
        // Check for HealthCertificate existence with same month and year
        if ($this->healthCertificateRepository->healthCertificateExist(
            $validatedRequest
        )) {
            throw ValidationException::withMessages([
                'health_certificate_exist' => ['برای این ماه از سال، شناسنامه سلامت، ایحاد شده است.']
            ]);
        }
        DB::beginTransaction();
        try {
            $healthCertificate = $this->healthCertificateRepository->create($validatedRequest);
            DB::commit();
            return $this->formatHealthCertificatePayload($healthCertificate);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'create_health_certificate' => 'خطا در هنگام ایجاد: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get all HealthCertificates
     *
     * @return array
     */
    public function getAllHealthCertificates()
    {
        $healthCertificates = $this->healthCertificateRepository->getAll();
        return $this->formatHealthCertificatesListPayload($healthCertificates);
    }

    /**
     * Update HealthCertificate
     *
     * @param int $id
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function updateHealthCertificate(int $id, array $data)
    {
        $healthCertificates = $this->healthCertificateRepository->update($id, $data);
        return $this->formatHealthCertificatePayload($healthCertificates);
    }

    /**
     * Delete HealthCertificate
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->healthCertificateRepository->delete($id);
    }

    /**
     * Get HealthCertificate
     *
     * @param int $id
     * @return array
     * @throws ModelNotFoundException
     */
    public function getHealthCertificate(int $id)
    {
        $healthCertificate = $this->healthCertificateRepository->findById($id);
        return [
            'id' => $healthCertificate->id,
            'name' => $healthCertificate->file_name,
            'month' => $healthCertificate->month,
            'year' => $healthCertificate->year,
            'status' => $healthCertificate->status,
            'uploadedBy' => $healthCertificate->uploadedBy ? [
                'id' => $healthCertificate->uploadedBy->id,
                'fullName' => $healthCertificate->uploadedBy->first_name . ' ' . $healthCertificate->uploadedBy->last_name,
                'username' => $healthCertificate->uploadedBy->username,
            ] : null,
            'editedBy' => $healthCertificate->editedBy ? [
                'id' => $healthCertificate->editedBy->id,
                'fullName' => $healthCertificate->editedBy->first_name . ' ' . $healthCertificate->editedBy->last_name,
                'username' => $healthCertificate->editedBy->username,
            ] : null,
            'users' => $healthCertificate->users->map(function ($hcUser) {
                return [
                    'id' => $hcUser->id,
                    'user' => $hcUser->user ? [
                        'id' => $hcUser->user->id,
                        'fullName' => $hcUser->user->first_name . ' ' . $hcUser->user->last_name,
                        'username' => $hcUser->user->username,
                    ] : null,
                    'image' => $hcUser->image,
                    'status' => $hcUser->status,
                    'uploadedBy' => $hcUser->uploadedBy ? [
                        'id' => $hcUser->uploadedBy->id,
                        'fullName' => $hcUser->uploadedBy->first_name . ' ' . $hcUser->uploadedBy->last_name,
                        'username' => $hcUser->uploadedBy->username,
                    ] : null,
                    'editedBy' => $hcUser->editedBy ? [
                        'id' => $hcUser->editedBy->id,
                        'fullName' => $hcUser->editedBy->first_name . ' ' . $hcUser->editedBy->last_name,
                        'username' => $hcUser->editedBy->username,
                    ] : null,
                ];
            })->toArray(),
            'createdAt' => $healthCertificate->created_at,
            'updatedAt' => $healthCertificate->updated_at,
        ];
    }

    /**
     * Format single HealthCertificate payload
     *
     * @param HealthCertificate $healthCertificate
     * @return array
     */
    protected function formatHealthCertificatePayload(HealthCertificate $healthCertificate): array
    {
        return [
            'id' => $healthCertificate->id,
            'name' => $healthCertificate->file_name,
            'month' => $healthCertificate->month,
            'year' => $healthCertificate->year,
            'status' => $healthCertificate->status,
            'uploadedBy' => $healthCertificate->uploadedBy ? [
                'id' => $healthCertificate->uploadedBy->id,
                'fullName' => $healthCertificate->uploadedBy->first_name . ' ' . $healthCertificate->uploadedBy->last_name,
                'username' => $healthCertificate->uploadedBy->username,
            ] : null,
            'editedBy' => $healthCertificate->editedBy ? [
                'id' => $healthCertificate->editedBy->id,
                'fullName' => $healthCertificate->editedBy->first_name . ' ' . $healthCertificate->editedBy->last_name,
                'username' => $healthCertificate->editedBy->username,
            ] : null,
            'createdAt' => $healthCertificate->created_at,
            'updatedAt' => $healthCertificate->updated_at,
        ];
    }

    /**
     * Format HealthCertificates list payload
     *
     * @param \Illuminate\Database\Eloquent\Collection $healthCertificates
     * @return array
     */
    protected function formatHealthCertificatesListPayload($healthCertificates): array
    {
        return [
            'healthCertificates' => $healthCertificates->map(function ($healthCertificate) {
                return $this->formatHealthCertificatePayload($healthCertificate);
            })->toArray(),
            'metadata' => [
                'total' => $healthCertificates->count(),
                'retrievedAt' => Carbon::now(),
            ],
        ];
    }
}
