<?php

namespace App\Repositories\Birthday;

use App\Models\Birthday\BirthdayGift;
use Illuminate\Support\Facades\Auth;

class BirthdayGiftRepository
{
    /**
     * create new birthday gift
     *
     * @param array $data
     * @return \App\Models\Birthday\BirthdayGift
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::id();
        $path = request()->file('image')->store('birthdayGifts', 'public');
        $data['image'] = $path;
        return BirthdayGift::create($data);
    }

    public function getAll()
    {
        return BirthdayGift::with('createdBy', 'editedBy')->get();
    }

    /**
     * Update BirthdayGift
     *
     * @param int $id
     * @param array $data
     * @return BirthdayGift
     */
    public function update(int $id, array $data)
    {
        $birthdayGift = $this->findById($id);
        $data['edited_by'] = Auth::id();
        if (request()->file('image')) {
            $path = request()->file('image')->store('birthdayGifts', 'public');
            $data['image'] = $path;
        }
        $birthdayGift->update($data);
        return $birthdayGift;
    }

    /**
     * Delete BirthdayGift
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        $birthdayGift = $this->findById($id);
        return $birthdayGift->delete();
    }

    /**
     * Get BirthdayGift by ID
     *
     * @param int $id
     * @return BirthdayGift
     * @throws ModelNotFoundException
     */
    public function findById(int $id): BirthdayGift
    {
        return BirthdayGift::findOrFail($id);
    }

    /**
     * Check if there's a gift with the same code
     *
     * @param array $data
     * @return bool
     */
    public function giftExist(array $data)
    {
        return BirthdayGift::where('code', $data['code'])->exists();
    }
}
