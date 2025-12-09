<?php

namespace App\Repositories\Contractor;

use Illuminate\Support\Facades\Auth;
use App\Models\Contractor\Contractor;

class ContractorRepository
{
    /**
     * create new contractor
     *
     * @param array $data
     * @return \App\Models\Contractor\Contractor
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::id();
        return Contractor::create($data);
    }

    /**
     * Get all contractors
     *
     * @return array
     */
    public function getAll()
    {
        return Contractor::with('createdBy', 'editedBy')->get();
    }

    /**
     * Get all active contractors
     *
     * @return array
     */
    public function getAllActive()
    {
        return Contractor::with('createdBy', 'editedBy')->where('active', 1)->get();
    }

    /**
     * Check if there's a contractor with the same national code
     *
     * @param array $data
     * @return bool
     */
    public function contractorExist(array $data)
    {
        return Contractor::where('national_code', $data['national_code'])->exists();
    }

}
