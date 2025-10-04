<?php

namespace App\Repositories\Birthday;

use App\Models\Birthday\BirthdayFileUser;
use Illuminate\Support\Facades\Auth;

class BirthdayFileUserRepository
{
    /**
     * create new BirthdayFileUser
     *
     * @param array $data
     * @return \App\Models\Birthday\BirthdayFileUser
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::id();
        $data['status'] = true;
        return BirthdayFileUser::create($data);
    }

    /**
     * Update BirthdayFileUser
     *
     * @param int $id
     * @param array $data
     * @return BirthdayFileUser
     */
    public function update(int $id, array $data)
    {
        $birthdayFileUser = $this->findById($id);
        $data['edited_by'] = Auth::id();
        $birthdayFileUser->update($data);
        return $birthdayFileUser;
    }

    /**
     * Delete BirthdayFileUser
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        $birthdayFileUser = $this->findById($id);
        return $birthdayFileUser->delete();
    }

    /**
     * Get BirthdayFileUser by ID
     *
     * @param int $id
     * @return BirthdayFileUser
     * @throws ModelNotFoundException
     */
    public function findById(int $id): BirthdayFileUser
    {
        return BirthdayFileUser::findOrFail($id);
    }

    /**
     * Check if there's a Birthday File User with the same Birthday File ID and User ID
     *
     * @param array $data
     * @return bool
     */
    public function UserExist(array $data)
    {
        return BirthdayFileUser::where('birthday_file_id', $data['birthday_file_id'])->where('user_id', $data['user_id'])->exists();
    }
}
