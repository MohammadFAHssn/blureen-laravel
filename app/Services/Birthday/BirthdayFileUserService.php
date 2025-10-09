<?php

namespace App\Services\Birthday;

use App\Models\Birthday\BirthdayFileUser;
use App\Models\User;
use App\Repositories\Birthday\BirthdayFileUserRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BirthdayFileUserService
{
    /**
     * @var BirthdayFileUserRepository
     */
    protected $birthdayFileUserRepository;

    /**
     * BirthdayFileUserService constructor
     *
     * @param BirthdayFileUserRepository $birthdayFileUserRepository
     */
    public function __construct(BirthdayFileUserRepository $birthdayFileUserRepository)
    {
        $this->birthdayFileUserRepository = $birthdayFileUserRepository;
    }

    public function createBirthdayFileUser($request)
    {
        $user = User::where('personnel_code', $request['code'])->first();
        if (!$user) {
            return ['reason' => 'کاربر یافت نشد', 'code' => $request['code']];
        }
        $request['user_id'] = $user->id;
        // Check for User existence with same Birthday File ID and User ID
        if ($this->birthdayFileUserRepository->UserExist($request)) {
            return;
        }
        $birthdayGiftUser = $this->birthdayFileUserRepository->create($request);
        return ['skipped' => false, 'data' => $this->formatBirthdayFileUserPayload($birthdayGiftUser)];
    }

    /**
     * Update BirthdayFileUser
     *
     * @param int $id
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function updateBirthdayFileUser(int $id, array $data)
    {
        $birthdayFileUser = $this->birthdayFileUserRepository->update($id, $data);
        return $this->formatBirthdayFileUserPayload($birthdayFileUser);
    }

    /**
     * Delete BirthdayFileUser
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->birthdayFileUserRepository->delete($id);
    }

    /**
     * Change Status of BirthdayFileUser
     *
     * @param int $id
     * @return \App\Models\BirthdayFileUser|null
     */
    public function changeStatus(int $id)
    {
        return $this->birthdayFileUserRepository->status($id);
    }

    /**
     * Choose a birthday gift for the current user.
     *
     * @param int $id
     * @return \App\Models\BirthdayFileUser
     */
    public function chooseBirthdayGift(int $id)
    {
        return $this->birthdayFileUserRepository->chooseBirthdayGift($id);
    }

    /**
     * Format single Birthday File User payload
     *
     * @param BirthdayFileUser $birthdayFileUser
     * @return array
     */
    protected function formatBirthdayFileUserPayload(BirthdayFileUser $birthdayFileUser): array
    {
        return [
            'id' => $birthdayFileUser->id,
            'birthdayFileId' => $birthdayFileUser->birthday_file_id,
            'userId' => $birthdayFileUser->user_id,
            'birthdayGiftId' => $birthdayFileUser->birthday_gift_id,
            'gift' => $birthdayFileUser->gift ? [
                'id' => $birthdayFileUser->gift->id,
                'name' => $birthdayFileUser->gift->name,
                'code' => $birthdayFileUser->gift->code,
            ] : null,
            'status' => $birthdayFileUser->status,
            'createdBy' => $birthdayFileUser->createdBy ? [
                'id' => $birthdayFileUser->createdBy->id,
                'fullName' => $birthdayFileUser->createdBy->first_name . ' ' . $birthdayFileUser->createdBy->last_name,
                'username' => $birthdayFileUser->createdBy->username,
            ] : null,
            'editedBy' => $birthdayFileUser->editedBy ? [
                'id' => $birthdayFileUser->editedBy->id,
                'fullName' => $birthdayFileUser->editedBy->first_name . ' ' . $birthdayFileUser->editedBy->last_name,
                'username' => $birthdayFileUser->editedBy->username,
            ] : null,
            'createdAt' => $birthdayFileUser->created_at,
            'updatedAt' => $birthdayFileUser->updated_at,
        ];
    }

    /**
     * Format Birthday Files list payload
     *
     * @param \Illuminate\Database\Eloquent\Collection $birthdayFileUsers
     * @return array
     */
    protected function formatBirthdayFileUsersListPayload($birthdayFileUsers): array
    {
        return [
            'birthdayFileUsers' => $birthdayFileUsers->map(function ($birthdayFileUser) {
                return $this->formatBirthdayFileUserPayload($birthdayFileUser);
            })->toArray(),
            'metadata' => [
                'total' => $birthdayFileUsers->count(),
                'retrievedAt' => Carbon::now(),
            ],
        ];
    }
}
