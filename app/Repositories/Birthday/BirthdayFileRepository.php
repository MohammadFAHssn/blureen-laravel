<?php

namespace App\Repositories\Birthday;

use App\Models\Birthday\BirthdayFile;
use Illuminate\Support\Facades\Auth;

class BirthdayFileRepository
{
    /**
     * create new BirthdayFile
     *
     * @param array $data
     * @return \App\Models\Birthday\BirthdayFile
     */
    public function create(array $data)
    {
        $data['uploaded_by'] = Auth::id();
        $data['status'] = true;
        return BirthdayFile::create($data);
    }

    /**
     * Get all BirthdayFiles
     *
     * @return array
     */
    public function getAll()
    {
        return BirthdayFile::with('uploadedBy', 'editedBy')->get();
    }

    /**
     * Update BirthdayFile
     *
     * @param int $id
     * @param array $data
     * @return BirthdayFile
     */
    public function update(int $id, array $data)
    {
        $birthdayFile = $this->findById($id);
        $data['edited_by'] = Auth::id();
        $birthdayFile->update($data);
        return $birthdayFile;
    }

    /**
     * Delete BirthdayFile
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        $birthdayFile = $this->findById($id);
        return $birthdayFile->delete();
    }

    /**
     * Get BirthdayFile by ID
     *
     * @param int $id
     * @return BirthdayFile
     * @throws ModelNotFoundException
     */
    public function findById(int $id): BirthdayFile
    {
        return BirthdayFile::findOrFail($id);
    }

    /**
     * Check if there's a Birthday File with the same month and year
     *
     * @param array $data
     * @return bool
     */
    public function fileExist(array $data)
    {
        return BirthdayFile::where('month', $data['month'])->where('year', $data['year'])->exists();
    }
}
