<?php

namespace App\Services\HSE;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\HSE\HealthCertificate;
use Illuminate\Validation\ValidationException;
use App\Repositories\HSE\HealthCertificateRepository;
use App\Models\User;
use App\Repositories\HSE\HealthCertificateUserRepository;

class HealthCertificateService
{
    /**
     * @var HealthCertificateRepository
     * @var HealthCertificateUserRepository
     */
    protected $healthCertificateRepository;
    protected $healthCertificateUserRepository;

    /**
     * HealthCertificateService constructor
     *
     * @param HealthCertificateRepository $healthCertificateRepository
     * @param HealthCertificateUserRepository $healthCertificateUserRepository
     */
    public function __construct(HealthCertificateRepository $healthCertificateRepository, HealthCertificateUserRepository $healthCertificateUserRepository)
    {
        $this->healthCertificateRepository = $healthCertificateRepository;
        $this->healthCertificateUserRepository = $healthCertificateUserRepository;
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
     * Show a specific healthCertificate’s users
     *
     * @return array
     */
    public function getHealthCertificateUsers()
    {
        return $this->healthCertificateRepository->getHealthCertificateUsers();
    }

    /**
     * @param int $healthCertificateId
     * @param int $year
     * @param UploadedFile[] $files
     * @return array{success: array<int, array>, skipped: array<int, array>}
     */
    public function addImages(int $healthCertificateId, int $year, array $files): array
    {
        $success = [];
        $skipped = [];

        foreach ($files as $file) {
            $original = $file->getClientOriginalName();
            $basename = pathinfo($original, PATHINFO_FILENAME); // e.g. "6543"
            $code = trim($basename);

            $user = User::where('personnel_code', $code)->first();

            if (!$user) {
                $skipped[] = [
                    'filename' => $original,
                    'reason' => 'user_not_found_by_personnel_code',
                    'personnel_code' => $code,
                ];
                continue;
            }

            $record = $this->healthCertificateUserRepository
                ->createOrReplaceForUser($healthCertificateId, $user->id, $year, $file);

            $success[] = [
                'filename' => $original,
                'personnel_code' => $code,
                'record_id' => $record->id,
            ];
        }

        return compact('success', 'skipped');
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
