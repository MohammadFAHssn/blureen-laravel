<?php

namespace App\Services\Birthday;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Birthday\BirthdayFile;
use Illuminate\Validation\ValidationException;
use App\Imports\Birthday\BirthDayFileUserImport;
use App\Repositories\Birthday\BirthdayFileRepository;


class BirthdayFileService
{
    /**
     * @var BirthdayFileRepository
     * @var BirthdayFileUserService
     */
    protected $birthdayFileRepository,
        $birthdayFileUserService;

    /**
     * BirthdayFileService constructor
     *
     * @param BirthdayFileRepository $birthdayFileRepository
     * @param BirthdayFileUserService $birthdayFileUserService
     */
    public function __construct(BirthdayFileRepository $birthdayFileRepository, BirthdayFileUserService $birthdayFileUserService)
    {
        $this->birthdayFileRepository = $birthdayFileRepository;
        $this->birthdayFileUserService = $birthdayFileUserService;
    }

    public function createBirthdayFile($request)
    {
        // Check for BirthdayFile existence with same month and year
        $validatedRequest = $request->validated();
        if ($this->birthdayFileRepository->fileExist(
            $validatedRequest
        )) {
            throw ValidationException::withMessages([
                'birthday_file_exist' => ['این فایل هدیه در پایگاه داده وجود دارد. برای هرماه از یک سال به خصوص، فقط یک فایل می‌تواند در پایگاه باشد.']
            ]);
        }

        $requiredHeaders = ['شماره پرسنلي'];
        $rows = Excel::toArray(null, $request->file('file'))[0] ?? [];

        // Empty file
        if (empty($rows) || count($rows) <= 1) {
            throw ValidationException::withMessages([
                'file' => 'فایل اکسل خالی است یا هیچ سطری برای پردازش ندارد.'
            ]);
        }

        // Check headers
        $uploadedHeaders = array_map('trim', $rows[0]);
        $diff = array_diff($requiredHeaders, $uploadedHeaders);

        if (!empty($diff)) {
            throw ValidationException::withMessages([
                'headers' => 'ستون‌های فایل اکسل نادرست هستند.',
                'missing_headers' => $diff,
            ]);
        }

        $file = $request->file('file');
        
        DB::beginTransaction();
        
        try {
            $birthdayFile = $this->birthdayFileRepository->create($validatedRequest);
            Excel::import(new BirthdayFileUserImport($birthdayFile->id), $file);

            DB::commit();
            return $this->formatBirthdayFilePayload($birthdayFile);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'file_import' => 'خطا در هنگام پردازش فایل اکسل: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get all BirthdayFiles
     *
     * @return array
     */
    public function getAllBirthdayFiles()
    {
        $birthdayFiles = $this->birthdayFileRepository->getAll();
        return $this->formatBirthdayFilesListPayload($birthdayFiles);
    }

    /**
     * Update BirthdayFile
     *
     * @param int $id
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function updateBirthdayFile(int $id, array $data)
    {
        $birthdayGift = $this->birthdayFileRepository->update($id, $data);
        return $this->formatBirthdayFilePayload($birthdayGift);
    }

    /**
     * Delete BirthdayFile
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->birthdayFileRepository->delete($id);
    }

    /**
     * Format single Birthday File payload
     *
     * @param BirthdayFile $birthdayFile
     * @return array
     */
    protected function formatBirthdayFilePayload(BirthdayFile $birthdayFile): array
    {
        return [
            'id' => $birthdayFile->id,
            'name' => $birthdayFile->file_name,
            'month' => $birthdayFile->month,
            'year' => $birthdayFile->year,
            'status' => $birthdayFile->status,
            'uploadedBy' => $birthdayFile->uploadedBy ? [
                'id' => $birthdayFile->uploadedBy->id,
                'firstName' => $birthdayFile->uploadedBy->first_name,
                'lastName' => $birthdayFile->uploadedBy->last_name,
                'username' => $birthdayFile->uploadedBy->username,
            ] : null,
            'editedBy' => $birthdayFile->editedBy ? [
                'id' => $birthdayFile->editedBy->id,
                'firstName' => $birthdayFile->editedBy->first_name,
                'lastName' => $birthdayFile->editedBy->last_name,
                'username' => $birthdayFile->editedBy->username,
            ] : null,
            'createdAt' => $birthdayFile->created_at,
            'updatedAt' => $birthdayFile->updated_at,
        ];
    }

    /**
     * Format Birthday Files list payload
     *
     * @param \Illuminate\Database\Eloquent\Collection $birthdayFiles
     * @return array
     */
    protected function formatBirthdayFilesListPayload($birthdayFiles): array
    {
        return [
            'birthdayGifts' => $birthdayFiles->map(function ($birthdayFile) {
                return $this->formatBirthdayFilePayload($birthdayFile);
            })->toArray(),
            'metadata' => [
                'total' => $birthdayFiles->count(),
                'retrievedAt' => Carbon::now(),
            ],
        ];
    }
}
