<?php

namespace App\Services\Contractor;

use App\Models\Contractor\Contractor;
use App\Repositories\Contractor\ContractorRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ContractorService
{
    /**
     * @var ContractorRepository
     */
    protected $contractorRepository;

    /**
     * ContractorService constructor
     *
     * @param ContractorRepository $contractorRepository
     */
    public function __construct(ContractorRepository $contractorRepository)
    {
        $this->contractorRepository = $contractorRepository;
    }

    /**
     * create new contractor
     *
     * @param array $data
     * @return \App\Models\Contractor\Contractor
     */
    public function createContractor($request)
    {
        // Check for Contractor existence with same national code
        if ($this->contractorRepository->contractorExist(
            $request
        )) {
            throw ValidationException::withMessages([
                'contractor_exist' => ['این پیمانکار، در پایگاه داده وجود دارد.']
            ]);
        }
        $contractor = $this->contractorRepository->create($request);
        return $this->formatContractorPayload($contractor);
    }

    /**
     * Get all contractors
     *
     * @return array
     */
    public function getAllContractors()
    {
        $contractors = $this->contractorRepository->getAll();
        return $this->formatContractorsListPayload($contractors);
    }

    /**
     * Get all active foods
     *
     * @return array
     */
    public function getAllActiveContractors()
    {
        $contractors = $this->contractorRepository->getAllActive();
        return $this->formatContractorsListPayload($contractors);
    }

    /**
     * Format single contractor payload
     *
     * @param Contractor $contractor
     * @return array
     */
    protected function formatContractorPayload(Contractor $contractor): array
    {
        return [
            'id' => $contractor->id,
            'fullName' => $contractor->first_name . ' ' . $contractor->last_name,
            'nationalCode' => $contractor->national_code,
            'mobileNumber' => $contractor->mobile_number,
            'active' => $contractor->active,
            'createdBy' => $contractor->createdBy ? [
                'id' => $contractor->createdBy->id,
                'fullName' => $contractor->createdBy->first_name . ' ' . $contractor->createdBy->last_name,
                'username' => $contractor->createdBy->username,
            ] : null,
            'editedBy' => $contractor->editedBy ? [
                'id' => $contractor->editedBy->id,
                'fullName' => $contractor->editedBy->first_name . ' ' . $contractor->editedBy->last_name,
                'username' => $contractor->editedBy->username,
            ] : null,
            'createdAt' => $contractor->created_at,
            'updatedAt' => $contractor->updated_at,
        ];
    }

    /**
     * Format contractors list payload
     *
     * @param \Illuminate\Database\Eloquent\Collection $contractors
     * @return array
     */
    protected function formatContractorsListPayload($contractors): array
    {
        return [
            'contractors' => $contractors->map(function ($contractor) {
                return $this->formatContractorPayload($contractor);
            })->toArray(),
            'metadata' => [
                'total' => $contractors->count(),
                'retrievedAt' => Carbon::now(),
            ],
        ];
    }
}
