<?php

namespace App\Repositories\Birthday;

use App\Models\Birthday\BirthdayFile;
use Illuminate\Support\Facades\Auth;
use App\Models\Birthday\BirthdayFileUser;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Birthday\BirthdayFileUserExport;

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
        return BirthdayFile::with('uploadedBy', 'editedBy', 'users.user', 'users.gift',)->get();
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
     * Get birthday file statistics
     *
     * @param int $id
     * @return bool
     */
    public function statistics($request)
    {
        $data = BirthdayFileUser::with('user', 'gift')->where('birthday_file_id', $request->id)->get()->toArray();
        info($data);
        return Excel::download(new BirthdayFileUserExport($data), 'birthday_file_statistics.xlsx');
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
