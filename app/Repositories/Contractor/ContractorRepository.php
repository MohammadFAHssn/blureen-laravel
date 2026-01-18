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
     * Get contractor by ID
     *
     * @param int $id
     * @return Contractor
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Contractor
    {
        return Contractor::findOrFail($id);
    }

    /**
     * change status of a contractor
     *
     * @return Contractor
     */
    public function status(int $id)
    {
        $contractor = $this->findById($id);
        if (!$contractor) {
            return null;
        }

        $contractor->active = !$contractor->active;
        $contractor->edited_by = Auth::id();
        $contractor->save();

        return $contractor;
    }
}
