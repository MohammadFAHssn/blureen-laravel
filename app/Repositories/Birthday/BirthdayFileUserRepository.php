<?php

namespace App\Repositories\Birthday;

use App\Models\Birthday\BirthdayFile;
use App\Models\Birthday\BirthdayFileUser;
use App\Models\Birthday\BirthdayGift;
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
        BirthdayFile::where('id', $data['birthday_file_id'])
            ->update(['edited_by' => Auth::id()]);
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
        if (!$birthdayFileUser)
            return null;
        return $birthdayFileUser->delete();
    }

    /**
     * Get BirthdayFileUser by ID
     *
     * @param int $id
     * @return BirthdayFileUser
     * @throws ModelNotFoundException
     */
    public function findById(int $id): ?BirthdayFileUser
    {
        return BirthdayFileUser::find($id);
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

    /**
     * Toggle Status of BirthdayFileUser
     *
     * @param int $id
     * @return \App\Models\BirthdayFileUser|null
     */
    public function status(int $id)
    {
        $birthdayFileUser = $this->findById($id);
        if (!$birthdayFileUser) {
            return null;
        }

        $birthdayFileUser->status = !$birthdayFileUser->status;
        $birthdayFileUser->save();

        return $birthdayFileUser;
    }

    /**
     * Let BirthdayFileUser choose a gift.
     *
     * @param int $id
     * @return \App\Models\BirthdayFileUser
     * @throws \RuntimeException|\InvalidArgumentException
     */
    public function chooseBirthdayGift(int $id)
    {
        $userId = Auth::id();

        $birthdayFileUser = BirthdayFileUser::where('user_id', $userId)
            ->where('status', true)
            ->whereHas('birthdayFile', fn($q) => $q->where('status', true))
            ->first();

        if (!$birthdayFileUser) {
            throw new \RuntimeException('کاربر در هیچ فایل فعالی عضو نیست.');
        }

        $newGift = BirthdayGift::find($id);
        if (!$newGift) {
            throw new \InvalidArgumentException('هدیه انتخابی وجود ندارد.');
        }

        if ($newGift->amount <= 0) {
            throw new \RuntimeException('موجودی هدیه انتخابی به اتمام رسید.');
        }

        if ($birthdayFileUser->birthday_gift_id) {
            $oldGift = BirthdayGift::find($birthdayFileUser->birthday_gift_id);
            if ($oldGift) {
                $oldGift->increment('amount');
            }
        }

        $newGift->decrement('amount');
        $birthdayFileUser->birthday_gift_id = $id;
        $birthdayFileUser->save();

        return $birthdayFileUser;
    }

    /**
     * Get the birthday_gift_id for the authenticated user
     * in the given BirthdayFile — if they have access.
     *
     * @param  int  $birthdayFileId
     * @return int|null  The birthday_gift_id if accessible, null if no access.
     */
    public function hasGiftAccess(int $birthdayFileId): ?int
    {
        $record = BirthdayFileUser::where('user_id', Auth::id())
            ->where('status', true)
            ->where('birthday_file_id', $birthdayFileId)
            ->first(['birthday_gift_id']);

        if (!$record) {
            return null;  // no access
        }

        return $record->birthday_gift_id ?: 0;  // 0 means access exists but no gift chosen
    }
}
