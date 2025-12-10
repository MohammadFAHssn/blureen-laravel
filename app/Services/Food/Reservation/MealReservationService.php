<?php

namespace App\Services\Food\Reservation;

use App\Models\Food\MealReservation;
use App\Repositories\Food\Kitchen\MealPlanRepository;
use App\Repositories\Food\Reservation\MealReservationDetailRepository;
use App\Repositories\Food\Reservation\MealReservationRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class MealReservationService
{
    /**
     * @var mealReservationRepository
     * @var mealReservationDetailRepository
     * @var mealPlanRepository
     */
    protected $mealReservationRepository;

    protected $mealReservationDetailRepository;
    protected $mealPlanRepository;

    /**
     * MealReservationService constructor
     *
     * @param MealReservationRepository $mealReservationRepository
     * @param MealReservationDetailRepository $mealReservationDetailRepository
     * @param MealPlanRepository $mealPlanRepository
     */
    public function __construct(MealReservationRepository $mealReservationRepository, MealReservationDetailRepository $mealReservationDetailRepository, MealPlanRepository $mealPlanRepository)
    {
        $this->mealReservationRepository = $mealReservationRepository;
        $this->mealReservationDetailRepository = $mealReservationDetailRepository;
        $this->mealPlanRepository = $mealPlanRepository;
    }

    /**
     * create new meal reservation
     *
     * @param array $data
     * @return array
     */
    public function createMealReservation($request)
    {
        $mealReservations = [];

        foreach ($request['date'] as $date) {
            // find meal plan
            $mealPlan = $this->mealPlanRepository->findByDateAndId($date, $request['meal_id']);

            // fallback food data if no meal plan
            if ($mealPlan) {
                $foodId = $mealPlan->food->id;
                $foodPrice = $mealPlan->food->price;
            } else {
                $foodId = 1;
                $foodPrice = 1;
            }

            // create reservation
            $payload = $request;
            $payload['date'] = $date;
            $mealReservation = $this->mealReservationRepository->create($payload);

            // base detail payload
            $baseDetail = [
                'meal_reservation_id' => $mealReservation->id,
                'food_id' => $foodId,
                'food_price' => $foodPrice,
                'delivery_status' => 0,
            ];

            $detail = $baseDetail;

            if ($request['reserve_type'] === 'personnel') {
                foreach ($request['personnel'] as $personnelId) {
                    $detail['reserved_for_personnel'] = $personnelId;
                    $detail['quantity'] = 1;
                    $this->mealReservationDetailRepository->create($detail);
                }
            } elseif ($request['reserve_type'] === 'contractor') {
                $detail['reserved_for_contractor'] = $request['contractor'];
                $detail['quantity'] = $request['quantity'];
                $this->mealReservationDetailRepository->create($detail);
            } else {
                $detail['quantity'] = $request['quantity'];
                $this->mealReservationDetailRepository->create($detail);
            }

            $mealReservations[] = $this->formatMealReservationPayload($mealReservation);
        }
        return $mealReservations;
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
            'date' => $mealReservation->date,
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
