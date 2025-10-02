<?php

namespace App\Services\Birthday;

use App\Models\Birthday\BirthdayGift;
use App\Repositories\Birthday\BirthdayGiftRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BirthdayGiftService
{
    protected $birthdayGiftRepository;

    public function __construct(BirthdayGiftRepository $birthdayGiftRepository)
    {
        $this->birthdayGiftRepository = $birthdayGiftRepository;
    }

    public function createBirthdayGift($request)
    {
        // Check for Gift existence with same code
        if ($this->birthdayGiftRepository->giftExist(
            $request
        )) {
            throw ValidationException::withMessages([
                'birthday_gift_code_exist' => ['این کد هدیه در پایگاه داده وجود دارد.کد، باید یکتا باشد.']
            ]);
        }
        $birthdayGift = $this->birthdayGiftRepository->create($request);
        return $this->formatBirthdayGiftPayload($birthdayGift);
    }

    /**
     * Get all BirthdayGifts
     *
     * @return array
     */
    public function getAllBirthdayGifts()
    {
        $birthdayGifts = $this->birthdayGiftRepository->getAll();
        return $this->formatBirthdayGiftsListPayload($birthdayGifts);
    }

    /**
     * Update BirthdayGift
     *
     * @param int $id
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function updateBirthdayGift(int $id, array $data)
    {
        $birthdayGift = $this->birthdayGiftRepository->update($id, $data);
        return $this->formatBirthdayGiftPayload($birthdayGift);
    }

    /**
     * Delete BirthdayGift
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->birthdayGiftRepository->delete($id);
    }

    /**
     * Format single Birthday Gift payload
     *
     * @param BirthdayGift $birthdayGift
     * @return array
     */
    protected function formatBirthdayGiftPayload(BirthdayGift $birthdayGift): array
    {
        return [
            'id' => $birthdayGift->id,
            'name' => $birthdayGift->name,
            'code' => $birthdayGift->code,
            'image' => $birthdayGift->image,
            'status' => $birthdayGift->status,
            'amount' => $birthdayGift->amount,
            'createdBy' => $birthdayGift->createdBy ? [
                'id' => $birthdayGift->createdBy->id,
                'firstName' => $birthdayGift->createdBy->first_name,
                'lastName' => $birthdayGift->createdBy->last_name,
                'username' => $birthdayGift->createdBy->username,
            ] : null,
            'editedBy' => $birthdayGift->editedBy ? [
                'id' => $birthdayGift->editedBy->id,
                'firstName' => $birthdayGift->editedBy->first_name,
                'lastName' => $birthdayGift->editedBy->last_name,
                'username' => $birthdayGift->editedBy->username,
            ] : null,
            'createdAt' => $birthdayGift->created_at,
            'updatedAt' => $birthdayGift->updated_at,
        ];
    }

    /**
     * Format Birthday Gifts list payload
     *
     * @param \Illuminate\Database\Eloquent\Collection $birthdayGifts
     * @return array
     */
    protected function formatBirthdayGiftsListPayload($birthdayGifts): array
    {
        return [
            'birthdayGifts' => $birthdayGifts->map(function ($birthdayGift) {
                return $this->formatBirthdayGiftPayload($birthdayGift);
            })->toArray(),
            'metadata' => [
                'total' => $birthdayGifts->count(),
                'retrievedAt' => Carbon::now(),
            ],
        ];
    }
}
