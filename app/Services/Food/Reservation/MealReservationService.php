<?php

namespace App\Services\Food\Reservation;

use App\Models\Food\MealReservation;
use App\Repositories\Food\Kitchen\MealPlanRepository;
use App\Repositories\Food\Reservation\MealReservationDetailRepository;
use App\Repositories\Food\Reservation\MealReservationRepository;
use App\Repositories\Base\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MealReservationService
{
    /**
     * @var mealReservationRepository
     * @var mealReservationDetailRepository
     * @var mealPlanRepository
     * @var userRepository
     */
    protected $mealReservationRepository;

    protected $mealReservationDetailRepository;
    protected $mealPlanRepository;
    protected $userRepository;

    /**
     * MealReservationService constructor
     *
     * @param MealReservationRepository $mealReservationRepository
     * @param MealReservationDetailRepository $mealReservationDetailRepository
     * @param MealPlanRepository $mealPlanRepository
     * @param UserRepository $userRepository
     */
    public function __construct(MealReservationRepository $mealReservationRepository, MealReservationDetailRepository $mealReservationDetailRepository, MealPlanRepository $mealPlanRepository, UserRepository $userRepository)
    {
        $this->mealReservationRepository = $mealReservationRepository;
        $this->mealReservationDetailRepository = $mealReservationDetailRepository;
        $this->mealPlanRepository = $mealPlanRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * create new meal reservation
     *
     * @param array $data
     * @return array
     */
    public function createMealReservation(array $request): array
    {
        $results = [];

        foreach ($request['date'] as $date) {
            $results[] = DB::transaction(function () use ($request, $date) {
                // Find meal plan (food fallback)
                $mealPlan = $this->mealPlanRepository->findByDateAndId($date, (int) $request['meal_id']);

                if ($mealPlan && $mealPlan->food) {
                    $foodId = (int) $mealPlan->food->id;
                    $foodPrice = (int) $mealPlan->food->price;
                } else {
                    $foodId = 1;
                    $foodPrice = 1;
                }

                // Prepare reservation payload
                $payload = $request;
                $payload['date'] = $date;

                // Base detail payload
                $baseDetail = [
                    'food_id' => $foodId,
                    'food_price' => $foodPrice,
                    'delivery_status' => 0,
                ];

                // --- PERSONNEL ---
                if ($request['reserve_type'] === 'personnel') {
                    $mealId = (int) $request['meal_id'];

                    // Existing reserved personnel for (date + meal_id)
                    $existingPersonnelIds = $this
                        ->mealReservationDetailRepository
                        ->reservedPersonnelIdsByDateAndMeal($date, $mealId)
                        ->all();

                    $createdPersonnel = [];
                    $skippedPersonnel = [];
                    // $skippedDate = [];

                    // De-dupe request input
                    $personnelIds = array_values(array_unique($request['personnel'] ?? []));

                    // Decide which ones will be created BEFORE creating reservation
                    $toCreate = [];
                    foreach ($personnelIds as $personnelId) {
                        $personnelId = (int) $personnelId;

                        if (in_array($personnelId, $existingPersonnelIds, true)) {
                            $personnel = $this->userRepository->findById($personnelId);
                            $skippedPersonnel[] = [
                                'full_name' => $personnel->first_name . ' ' . $personnel->last_name,
                                'date' => $date,
                            ];
                            continue;
                        }

                        $toCreate[] = $personnelId;
                        $existingPersonnelIds[] = $personnelId;  // prevent duplicates inside same request
                    }

                    // If all are skipped, don't create empty reservation
                    if (count($toCreate) === 0) {
                        return [
                            'reservation' => null,
                            'createdPersonnel' => [],
                            'skippedPersonnel' => $skippedPersonnel,
                            'date' => $date,
                            'meal_id' => $mealId,
                        ];
                    }

                    // Create reservation once
                    $mealReservation = $this->mealReservationRepository->create($payload);

                    // Create details
                    foreach ($toCreate as $personnelId) {
                        $detail = $baseDetail + [
                            'meal_reservation_id' => $mealReservation->id,
                            'reserved_for_personnel' => $personnelId,
                            'quantity' => 1,
                        ];

                        $this->mealReservationDetailRepository->create($detail);
                        $createdPersonnel[] = $personnelId;
                    }

                    return [
                        'reservation' => $this->formatMealReservationPayload($mealReservation),
                        'createdPersonnel' => $createdPersonnel,
                        'skippedPersonnel' => $skippedPersonnel,
                    ];
                }

                // --- CONTRACTOR ---
                if ($request['reserve_type'] === 'contractor') {
                    $mealReservation = $this->mealReservationRepository->create($payload);

                    $detail = $baseDetail + [
                        'meal_reservation_id' => $mealReservation->id,
                        'reserved_for_contractor' => (int) $request['contractor'],
                        'quantity' => (int) $request['quantity'],
                    ];

                    $this->mealReservationDetailRepository->create($detail);

                    return [
                        'reservation' => $this->formatMealReservationPayload($mealReservation),
                    ];
                }

                // --- GUEST (default) ---
                $mealReservation = $this->mealReservationRepository->create($payload);

                $detail = $baseDetail + [
                    'meal_reservation_id' => $mealReservation->id,
                    'quantity' => (int) $request['quantity'],
                ];

                $this->mealReservationDetailRepository->create($detail);

                return [
                    'reservation' => $this->formatMealReservationPayload($mealReservation),
                ];
            });
        }

        return $results;
    }

    /**
     * Get all meal reservations for personnel by a user on date
     *
     * @param array $data
     * @return array
     */
    public function getAllMealReservationsForPersonnelByUserOnDate($request)
    {
        $mealReservationsForAllDates = [];
        foreach ($request['date'] as $date) {
            $mealReservationsForDate = $this->mealReservationRepository->getAllForPersonnelByUserOnDate($date);
            $mealReservationsForAllDates[] = $mealReservationsForDate;
        }
        return $mealReservationsForAllDates;
    }

    /**
     * Get all meal reservations for a user on date
     *
     * @param array $data
     * @return array
     */
    public function getAllMealReservationsForUserByOthersOnDate($request)
    {
        $result = [];

        foreach ($request['date'] as $date) {
            // Get all reservations for this date
            $reservations = $this->mealReservationRepository->findByDate($date);

            foreach ($reservations as $reservation) {
                // Load details for this reservation ID
                $details = $this->mealReservationDetailRepository->findByReservationIdUserId($reservation->id);
                if ($details->isNotEmpty()) {
                    // $result[] = $details;
                    // /////////////////////////
                    foreach ($details as $detail) {
                        $result[] = [
                            'createdBy' => trim(
                                ($detail->createdBy->first_name ?? '') . ' '
                                . ($detail->createdBy->last_name ?? '')
                            ),
                            'personnelCode' => $detail->createdBy->personnel_code ?? null,
                            'deliveryStatus' => $detail->delivery_status ?? null,
                            'reservation' => $detail->reservation ? [
                                'mealName' => $detail->reservation->meal->name ?? null,
                                'date' => $detail->reservation->date?->format('Y/m/d'),
                            ] : null,
                        ];
                    }
                    // /////////////////////////
                }
            }
        }

        return $result;
    }

    /**
     * Get all meal reservations for contractor by a user on date
     *
     * @param array $data
     * @return array
     */
    public function getAllMealReservationsForContractorByUserOnDate($request)
    {
        $mealReservationsForAllDates = [];
        foreach ($request['date'] as $date) {
            $mealReservationsForDate = $this->mealReservationRepository->getAllForContractorByUserOnDate($date);
            $mealReservationsForAllDates[] = $mealReservationsForDate;
        }
        return $mealReservationsForAllDates;
    }

    /**
     * Get all meal reservations for guest by a user on date
     *
     * @param array $data
     * @return array
     */
    public function getAllMealReservationsForGuestByUserOnDate($request)
    {
        $mealReservationsForAllDates = [];
        foreach ($request['date'] as $date) {
            $mealReservationsForDate = $this->mealReservationRepository->getAllForGuestByUserOnDate($date);
            $mealReservationsForAllDates[] = $mealReservationsForDate;
        }
        return $mealReservationsForAllDates;
    }

    /**
     * Get all meal reservations for repairman by a user on date
     *
     * @param array $data
     * @return array
     */
    public function getAllMealReservationsForRepairmanByUserOnDate($request)
    {
        $mealReservationsForAllDates = [];
        foreach ($request['date'] as $date) {
            $mealReservationsForDate = $this->mealReservationRepository->getAllForRepairmanByUserOnDate($date);
            $mealReservationsForAllDates[] = $mealReservationsForDate;
        }
        return $mealReservationsForAllDates;
    }

    /**
     * Get all delivered meal reservations for a specific contractor in a date range
     *
     * @param array $request
     * @return \Illuminate\Support\Collection
     */
    public function getAllDeliveredMealReservationsForContractorOnDate(array $request)
    {
        return $this
            ->mealReservationRepository
            ->getAllDeliveredForContractorBetweenDates(
                $request['date'][0],
                $request['date'][1],
                $request['contractor']
            );
    }

    /**
     * Get all meal reservations in a date range
     *
     * @param array $request
     * @return boolean
     */
    public function getAllMealReservationsInDateRange(array $request)
    {
        return $this
            ->mealReservationRepository
            ->getAllBetweenDates(
                $request['date'][0],
                $request['date'][1]
            );
    }

    /**
     * check to see if there is even one delivered meal reservation in a date
     *
     * @param array $request
     * @return boolean
     */
    public function checkForDelivered(array $request)
    {
        return $this
            ->mealReservationRepository
            ->checkForDelivered($request);
    }

    /**
     * Update meal reservation
     *
     * @param int $id
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function updateMealReservation(int $id, array $data)
    {
        $mealReservations = $this->mealReservationRepository->update($id, $data);
        return $this->formatMealReservationPayload($mealReservations);
    }

    /**
     * Delete meal reservation
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->mealReservationRepository->delete($id);
    }

    /**
     * Format single meal reservation payload
     *
     * @param MealReservation $mealReservation
     * @return array
     */
    protected function formatMealReservationPayload(MealReservation $mealReservation): array
    {
        return [
            'date' => $mealReservation->date?->format('Y/m/d'),
            'meal' => $mealReservation->meal ? [
                'id' => $mealReservation->meal->id,
                'name' => $mealReservation->meal->name,
            ] : null,
            'supervisor' => $mealReservation->supervisor ? [
                'personneCode' => $mealReservation->supervisor->personnel_code,
                'fullName' => $mealReservation->supervisor->first_name . $mealReservation->supervisor->last_name,
            ] : null,
            //
            'details' => $mealReservation->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'reservedForPersonnel' => $detail->personnel ? [
                        'fullName' => $detail->personnel->first_name . $detail->personnel->last_name,
                        'personnelCode' => $detail->personnel->personnel_code,
                    ] : null,
                    'reservedForContractor' => $detail->contractor ? [
                        'fullName' => $detail->contractor->first_name . $detail->contractor->last_name,
                        'nationalCode' => $detail->contractor->national_code,
                        'mobileNumber' => $detail->contractor->mobile_number,
                    ] : null,
                    'food' => [
                        'id' => $detail->food_id,
                        'quantity' => $detail->quantity,
                    ],
                    'deliveryStatus' => $detail->delivery_status,
                ];
            })->toArray(),
            //
            'createdBy' => $mealReservation->createdBy ? [
                'id' => $mealReservation->createdBy->id,
                'fullName' => $mealReservation->createdBy->first_name . ' ' . $mealReservation->createdBy->last_name,
                'username' => $mealReservation->createdBy->username,
            ] : null,
            'editedBy' => $mealReservation->editedBy ? [
                'id' => $mealReservation->editedBy->id,
                'fullName' => $mealReservation->editedBy->first_name . ' ' . $mealReservation->editedBy->last_name,
                'username' => $mealReservation->editedBy->username,
            ] : null,
            'createdAt' => $mealReservation->created_at,
            'updatedAt' => $mealReservation->updated_at,
        ];
    }

    /**
     * Format meal reservations list payload
     *
     * @param \Illuminate\Database\Eloquent\Collection $mealReservations
     * @return array
     */
    protected function formatMealReservationsListPayload($mealReservations): array
    {
        return [
            'mealReservations' => $mealReservations->map(function ($mealReservation) {
                return $this->formatMealReservationPayload($mealReservation);
            })->toArray(),
            'metadata' => [
                'total' => $mealReservations->count(),
                'retrievedAt' => Carbon::now(),
            ],
        ];
    }
}
