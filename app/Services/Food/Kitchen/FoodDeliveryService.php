<?php

namespace App\Services\Food\Kitchen;

use App\Models\Food\MealReservation;
use App\Repositories\Base\UserRepository;
use App\Repositories\Food\Kitchen\FoodRepository;
use App\Repositories\Food\Reservation\MealReservationDetailRepository;
use App\Repositories\Food\Reservation\MealReservationRepository;
use App\Services\Api\KasraService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FoodDeliveryService
{
    /**
     * @var MealReservationRepository
     * @var MealReservationDetailRepository
     * @var FoodRepository
     */
    protected $mealReservationRepository;

    protected $mealReservationDetailRepository;
    protected $foodRepository;
    protected $kasraService;
    protected $userRepository;

    /**
     * FoodDeliveryService constructor
     *
     * @param MealReservationRepository $mealReservationRepository
     * @param MealReservationDetailRepository $mealReservationDetailRepository
     * @param FoodRepository $foodRepository
     */
    public function __construct(MealReservationRepository $mealReservationRepository, MealReservationDetailRepository $mealReservationDetailRepository, FoodRepository $foodRepository)
    {
        $this->mealReservationRepository = $mealReservationRepository;
        $this->mealReservationDetailRepository = $mealReservationDetailRepository;
        $this->foodRepository = $foodRepository;
        $this->kasraService = new KasraService();
        $this->userRepository = new UserRepository();
    }

    /**
     * deliver a meal reservation
     *
     * @param array $data
     * @return MealReservation|null
     * @throws ValidationException
     */
    public function deliverMealReservation($data)
    {
        if ($data['type'] === 'personnel') {
            $personnelIds = $this
                ->mealReservationDetailRepository
                ->personnelIds($data['reserved_meal_id'], $data['noDeliveryFor'] ?? []);
            $reservation = $this->mealReservationRepository->findById($data['reserved_meal_id']);
            $this->checkingPersonnelEntry($personnelIds, $this->jalaliDate($reservation->date));
        }

        return DB::transaction(function () use ($data) {
            $mealReservation = $this->mealReservationRepository->deliver($data['reserved_meal_id']);

            if ($data['type'] === 'personnel') {
                $this->mealReservationDetailRepository->markDeliveredExcept(
                    $data['reserved_meal_id'],
                    $data['noDeliveryFor'] ?? []
                );
                return $mealReservation;
            }

            if ($data['type'] === 'guest' || $data['type'] === 'repairman') {
                $this->mealReservationDetailRepository->markDeliveredExcept($data['reserved_meal_id']);
                return $mealReservation;
            }

            // contractor
            if ($data['today_food_count'] === 0) {
                $mealReservationDetail = $this->mealReservationDetailRepository->findByMealReservationId($data['reserved_meal_id']);
                $mealReservationDetail->delete();
            }
            else {
                $this->mealReservationDetailRepository->update($data['reserved_meal_id'], [
                    'quantity' => $data['today_food_count'],
                    'delivery_status' => 1,
                ]);
            }

            if (isset($data['second_food'])) {
                $secondFood = $this->foodRepository->findById($data['second_food']);
                $this->mealReservationDetailRepository->create([
                    'meal_reservation_id' => $data['reserved_meal_id'],
                    'food_id' => $secondFood->id,
                    'food_price' => $secondFood->price,
                    'delivery_status' => 1,
                    'reserved_for_contractor' => $data['contractor'],
                    'quantity' => $data['second_food_received_count'],
                ]);
            }

            if (isset($data['third_food'])) {
                $thirdFood = $this->foodRepository->findById($data['third_food']);
                $this->mealReservationDetailRepository->create([
                    'meal_reservation_id' => $data['reserved_meal_id'],
                    'food_id' => $thirdFood->id,
                    'food_price' => $thirdFood->price,
                    'delivery_status' => 1,
                    'reserved_for_contractor' => $data['contractor'],
                    'quantity' => $data['third_food_received_count'],
                ]);
            }

            return $mealReservation;
        });
    }

    /**
     * Throws if any personnel has no attendance on that date.
     *
     * @param \Illuminate\Support\Collection<int, int>|int[] $personnelIds
     * @param string $date  Jalali date string like 1404/10/06
     * @throws \Illuminate\Validation\ValidationException
     */
    public function checkingPersonnelEntry($personnelIds, string $date): void
    {
        $personnelIds = collect($personnelIds)->values();

        $notEnteredCodes = [];

        $codesById = $this->userRepository->personnelCodesByIds($personnelIds->all());

        foreach ($personnelIds as $personnelId) {
            $report = $this->kasraService->getEmployeeAttendanceReport([
                'start_date' => $date,
                'end_date' => $date,
                'user_id' => $personnelId,
            ]);

            $attendances = $report['attendances'] ?? [];

            if (empty($attendances)) {
                $notEnteredCodes[] = $codesById[$personnelId] ?? (string) $personnelId;
            }
        }

        if ($notEnteredCodes) {
            throw ValidationException::withMessages([
                'personnel' => ['Not entered: ' . implode(', ', $notEnteredCodes)],
            ]);
        }
    }

    private function jalaliDate($date): string
    {
        return is_string($date) ? $date : \Carbon\Carbon::parse($date)->format('Y/m/d');
    }

    /**
     * find a meal reservation by date and delivery code
     *
     * @param array $data
     * @return MealReservation|null
     * @throws ValidationException
     */
    public function findMealReservation(array $data)
    {
        $mealReservation = $this->mealReservationRepository->findByDateAndDeliveryCode($data);
        return $mealReservation;
    }

    /**
     * Get all meal reservations on a date
     *
     * @param array $data
     * @return Collection|null
     * @throws ValidationException
     */
    public function getAllMealReservationsOnDate(array $data)
    {
        return $this->mealReservationRepository->getAllOnDate($data);
    }
}
