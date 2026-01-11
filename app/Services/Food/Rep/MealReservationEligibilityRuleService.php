<?php

namespace App\Services\Food\Rep;

use App\Models\Food\MealReservationEligibilityRule;
use App\Repositories\Food\Rep\MealReservationEligibilityRuleRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class MealReservationEligibilityRuleService
{
    /**
     * @var mealReservationEligibilityRuleRepository
     */
    protected $mealReservationEligibilityRuleRepository;

    /**
     * MealReservationEligibilityRuleService constructor
     *
     * @param MealReservationEligibilityRuleRepository $mealReservationEligibilityRuleRepository
     */
    public function __construct(MealReservationEligibilityRuleRepository $mealReservationEligibilityRuleRepository)
    {
        $this->mealReservationEligibilityRuleRepository = $mealReservationEligibilityRuleRepository;
    }

    /**
     * create new Meal Reservation Eligibility Rule
     *
     * @param array $data
     * @return \App\Models\Food\MealReservationEligibilityRule
     */
    public function createMealReservationEligibilityRule($request)
    {
        // Check for meal reservation eligibility rule with same meal
        if ($this->mealReservationEligibilityRuleRepository->mealReservationEligibilityExist(
            $request['meal_id']
        )) {
            throw ValidationException::withMessages([
                'meal_reservation_eligibility_rule_exist' => ['برای این وعده غذایی، محدودیت زمانی وجود دارد.']
            ]);
        }
        $mealReservationEligibilityRule = $this->mealReservationEligibilityRuleRepository->create($request);
        return $this->formatMealReservationEligibilityRulePayload($mealReservationEligibilityRule);
    }

    /**
     * Get all Meal Reservation Eligibility Rules
     *
     * @return array
     */
    public function getAllMealReservationEligibilityRules()
    {
        $mealReservationEligibilityRules = $this->mealReservationEligibilityRuleRepository->getAll();
        return $this->formatMealReservationEligibilityRulesListPayload($mealReservationEligibilityRules);
    }

    /**
     * Update Reservation Eligibility Rule
     *
     * @param int $id
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function updateMealReservationEligibilityRule(int $id, array $data)
    {
        $mealReservationEligibilityRule = $this->mealReservationEligibilityRuleRepository->update($id, $data);
        return $this->formatMealReservationEligibilityRulePayload($mealReservationEligibilityRule);
    }

    /**
     * Delete Reservation Eligibility Rule
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->mealReservationEligibilityRuleRepository->delete($id);
    }

    /**
     * Format single Reservation Eligibility Rule payload
     *
     * @param MealReservationEligibilityRule $mealReservationEligibilityRule
     * @return array
     */
    protected function formatMealReservationEligibilityRulePayload(MealReservationEligibilityRule $mealReservationEligibilityRule): array
    {
        return [
            'id' => $mealReservationEligibilityRule->id,
            'meal' => $mealReservationEligibilityRule->meal ? [
                'id' => $mealReservationEligibilityRule->meal->id,
                'name' => $mealReservationEligibilityRule->meal->name,
            ] : null,
            'time' => $mealReservationEligibilityRule->time,
            'createdBy' => $mealReservationEligibilityRule->createdBy ? [
                'id' => $mealReservationEligibilityRule->createdBy->id,
                'fullName' => $mealReservationEligibilityRule->createdBy->first_name . ' ' . $mealReservationEligibilityRule->createdBy->last_name,
                'username' => $mealReservationEligibilityRule->createdBy->username,
            ] : null,
            'editedBy' => $mealReservationEligibilityRule->editedBy ? [
                'id' => $mealReservationEligibilityRule->editedBy->id,
                'fullName' => $mealReservationEligibilityRule->editedBy->first_name . ' ' . $mealReservationEligibilityRule->editedBy->last_name,
                'username' => $mealReservationEligibilityRule->editedBy->username,
            ] : null,
            'createdAt' => $mealReservationEligibilityRule->created_at,
            'updatedAt' => $mealReservationEligibilityRule->updated_at,
        ];
    }

    /**
     * Format Reservation Eligibility Rules list payload
     *
     * @param \Illuminate\Database\Eloquent\Collection $mealReservationEligibilityRules
     * @return array
     */
    protected function formatMealReservationEligibilityRulesListPayload($mealReservationEligibilityRules): array
    {
        return [
            'mealReservationEligibilityRules' => $mealReservationEligibilityRules->map(function ($mealReservationEligibilityRule) {
                return $this->formatMealReservationEligibilityRulePayload($mealReservationEligibilityRule);
            })->toArray(),
            'metadata' => [
                'total' => $mealReservationEligibilityRules->count(),
                'retrievedAt' => Carbon::now(),
            ],
        ];
    }
}
