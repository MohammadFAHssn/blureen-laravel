<?php

namespace App\Services\Food\Rep;

use App\Repositories\Food\Rep\MealReservationEligibilityRuleRepository;
use App\Repositories\Food\Rep\MealReservationExceptionRepository;
use App\Repositories\Food\Reservation\MealReservationDetailRepository;
use Illuminate\Validation\ValidationException;

class MealReservationContradictionService
{
    /**
     * @var MealReservationDetailRepository
     * @var MealReservationEligibilityRuleRepository
     * @var MealReservationExceptionRepository
     */
    protected $mealReservationDetailRepository;

    protected $mealReservationEligibilityRuleRepository;
    protected $mealReservationExceptionRepository;

    /**
     * MealReservationContradictionService constructor
     */
    public function __construct()
    {
        $this->mealReservationDetailRepository = new MealReservationDetailRepository();
        $this->mealReservationEligibilityRuleRepository = new MealReservationEligibilityRuleRepository();
        $this->mealReservationExceptionRepository = new MealReservationExceptionRepository();
    }

    /**
     * Get all non-entitled delivered reservation details (personnel) in a date range for a meal.
     *
     * Non-entitled = delivered to personnel whose check_out_time is null or earlier than rule time.
     *
     * @param array{date: array{0:string,1:string}, meal_id:int} $data
     * @return \Illuminate\Support\Collection<int,\App\Models\MealReservationDetail>
     * @throws \Illuminate\Validation\ValidationException
     */
    public function nonEntitledReservationDetailsByDateRangeAndMeal(array $data)
    {
        $rule = $this->mealReservationEligibilityRuleRepository->findByMealId($data['meal_id']);
        if (!$rule) {
            throw ValidationException::withMessages([
                'meal_reservation_eligibility_rule_does_not_exist' => ['برای این وعده غذایی، محدودیت زمانی وجود ندارد.']
            ]);
        }

        $from = $data['date'][0];
        $to = $data['date'][1];

        // normalize '15:33' -> '15:33:00'
        $cutoffTime = preg_match('/^\d{2}:\d{2}$/', $rule->time)
            ? $rule->time . ':00'
            : $rule->time;

        $exceptionUserIds = $this->mealReservationExceptionRepository->getAllActiveUserIds($data['meal_id']);
        return $this->mealReservationDetailRepository->nonEntitledDeliveredReservationDetailsByDateRangeAndMeal($from, $to, (int) $data['meal_id'], $cutoffTime, $exceptionUserIds);
    }

    /**
     * Get delivered personnel reservation details that still need a checkout lookup.
     *
     * Criteria:
     * - delivery_status = 1
     * - check_out_time is NULL
     * - last_check_at is NULL OR last_check_at is older than 2 days
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\MealReservationDetail>
     */
    public function reservationDetailsNeedingCheckoutCheck()
    {
        return $this->mealReservationDetailRepository->personnelReservationDetailsNeedingCheckoutCheck();
    }
}
