<?php

namespace App\Services\HrRequest;


use App\Constants\AppConstants;
use App\Exceptions\CustomException;
use App\Models\Base\Setting;
use App\Models\HrRequest\HrRequest;
use App\Repositories\HrRequest\HrRequestDetailRepository;
use App\Repositories\HrRequest\HrRequestRepository;
use App\Services\Api\KasraService;
use App\Services\Base\ApprovalFlowService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\App;
use Morilog\Jalali\Jalalian;

class HrRequestService
{
    protected HrRequestRepository $hrRequestRepository;
    protected HrRequestDetailRepository $hrRequestDetailRepository;
    protected ApprovalFlowService $approvalFlowService;
    protected HrRequestApprovalService $hrRequestApprovalService;
    protected KasraService $kasraService;

    public function __construct(
        HrRequestRepository       $hrRequestRepository,
        HrRequestDetailRepository $hrRequestDetailRepository,
        ApprovalFlowService       $approvalFlowService,
        HrRequestApprovalService  $hrRequestApprovalService,
        KasraService              $kasraService
    )
    {
        $this->hrRequestRepository = $hrRequestRepository;
        $this->hrRequestDetailRepository =$hrRequestDetailRepository;
        $this->approvalFlowService = $approvalFlowService;
        $this->hrRequestApprovalService = $hrRequestApprovalService;
        $this->kasraService = $kasraService;
    }

    /**
     * @throws CustomException
     * @throws Exception
     */
    public function create(array $data)
    {
        $formTypeId = $data['request_type_id'];
        $userId = $data['user_id'];

        $userApprovalFlows = $this->approvalFlowService->getUserApprovalFlow($userId, $formTypeId);
        if ($userApprovalFlows->isEmpty()) {
            throw new CustomException('برای این درخواست رده تاییدیه تعریف نشده است.', 403);
        }


        return match ($formTypeId) {
            AppConstants::HR_REQUEST_TYPES['DAILY_LEAVE'] => $this->createDailyLeaveRequest($data, $userApprovalFlows),
            AppConstants::HR_REQUEST_TYPES['HOURLY_LEAVE'] => $this->createHourlyLeaveRequest($data, $userApprovalFlows),
            AppConstants::HR_REQUEST_TYPES['OVERTIME'] => $this->createOvertimeRequest($data, $userApprovalFlows),
            AppConstants::HR_REQUEST_TYPES['SICK'] => $this->createSickRequest($data, $userApprovalFlows),
            default => throw new Exception('فرم ارسالی نامعتبر است', 400),
        };
    }

    /**
     * @throws CustomException
     * @throws ConnectionException
     * @throws Exception
     */
    protected function createDailyLeaveRequest(array $data, Collection $userApprovalFlows)
    {
        $startDate = Jalalian::fromFormat('Y-m-d', $data['start_date'])->toCarbon()->startOfDay();
        $endDate = Jalalian::fromFormat('Y-m-d', $data['end_date'])->toCarbon()->startOfDay();
        $leaveRequestDuration = ($startDate->diffInDays($endDate)+1)*AppConstants::WORK_DAY_MINUTES;
        $data['details'] = [
            'duration' => $leaveRequestDuration
        ];

        if(!$this->userHasEnoughLeaveBalance($data['user_id'],$leaveRequestDuration)){
            throw new Exception('مانده مرخصی کافی نمیباشد.',403);
        }


        $hrRequest = $this->hrRequestRepository->create($data);
        $this->hrRequestApprovalService->create($hrRequest->id, $userApprovalFlows);
        return true;
    }

    /**
     * @throws Exception
     */

    protected function createHourlyLeaveRequest(array $data, Collection $userApprovalFlows)
    {
        $startInCarbon = Carbon::createFromFormat('H:i', $data['start_time']);
        $endInCarbon = Carbon::createFromFormat('H:i', $data['end_time']);

        $currentRequestDuration = $this->calculateDiffInMinutes($startInCarbon, $endInCarbon);
        if ($endInCarbon->lt($startInCarbon)) {
            $data['end_date'] = Jalalian::fromFormat('Y-m-d', $data['start_date'])
                ->addDay()->format('Y-m-d');
        }
        $data['details'] = [
            'duration' => $currentRequestDuration,
        ];

        $existingRequests =
            $this->hrRequestRepository
                ->getUserHourlyLeaveRequestsOfDay($data['user_id'], $data['start_date']);

        $otherHourlyRequestsDuration = 0;
        foreach ($existingRequests as $request) {
            $startInCarbon = Carbon::createFromFormat('H:i:s', $request['start_time']);
            $endInCarbon = Carbon::createFromFormat('H:i:s', $request['end_time']);
            $otherHourlyRequestsDuration += $this->calculateDiffInMinutes($startInCarbon, $endInCarbon);
        }

        if ($currentRequestDuration + $otherHourlyRequestsDuration > AppConstants::MAX_HOURLY_LEAVE_MINUTES) {
            throw new Exception('مجموع مرخصی ساعتی در یک روز نمی‌تواند بیشتر از ۳ ساعت و ۳۰ دقیقه باشد.', 403);
        }

        if(!$this->userHasEnoughLeaveBalance($data['user_id'],$currentRequestDuration)){
            throw new Exception('مانده مرخصی کافی نمیباشد.',403);
        }


        $hrRequest = $this->hrRequestRepository->create($data);
        $this->hrRequestApprovalService->create($hrRequest->id, $userApprovalFlows);

        return true;
    }


    protected function createOvertimeRequest(array $data, Collection $userApprovalFlows): true
    {
        $startInCarbon = Carbon::createFromFormat('H:i', $data['start_time']);
        $endInCarbon = Carbon::createFromFormat('H:i', $data['end_time']);
        if ($endInCarbon->lt($startInCarbon)) {
            $data['end_date'] = Jalalian::fromFormat('Y-m-d', $data['start_date'])
                ->addDay()->format('Y-m-d');
        }
        $hrRequest = $this->hrRequestRepository->create($data);
        $this->hrRequestApprovalService->create($hrRequest->id, $userApprovalFlows);
        return true;
    }

    protected function createSickRequest(array $data, Collection $userApprovalFlows): true
    {
        $hrRequest = $this->hrRequestRepository->create($data);
        $this->hrRequestApprovalService->create($hrRequest->id, $userApprovalFlows);
        return true;
    }


    public function getUserRequestsOfCurrentMonth($data): \Illuminate\Support\Collection
    {
        $jNow = Jalalian::now();
        $jy = $jNow->getYear();
        $jm = $jNow->getMonth();

        $start = sprintf('%04d-%02d-01', $jy, $jm);
        $end = sprintf('%04d-%02d-%02d', $jy, $jm, $jNow->getMonthDays());


        return HrRequest::
        where([
            'user_id' => $data['user_id'],
            'request_type_id' => $data['request_type']
        ])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end]);
            })
            ->orderBy('start_date')
            ->with('status')
            ->get();
    }


    public function update($data)
    {
        return $data;
    }

    /*
     * helper functions
     * */
    private function calculateDiffInMinutes(Carbon $start, Carbon $end): int
    {
        if ($end->lt($start)) {
            $toEndOfDay = $start->diffInMinutes($start->copy()->endOfDay());
            $fromMidnightToEnd = $end->diffInMinutes($end->copy()->startOfDay());
            return $toEndOfDay + $fromMidnightToEnd;
        }

        return $start->diffInMinutes($end);
    }


    /**
     * @throws CustomException
     * @throws ConnectionException
     */
    function userHasEnoughLeaveBalance($userId, $requestDurationInMin): bool
    {
        $kasraResponse = $this->kasraService->getRemainingLeave($userId);
        $remainingLeave = $this->convertRemainingLeaveToMinutes($kasraResponse['remaining_leave']);
        $totalPendingLeaveRequestDurationInMin = $this->hrRequestDetailRepository->getAllPendingLeaveRequestDuration($userId);
        $maxNegativeLeaveMinutes = (int) (
            Setting::query()
                ->where('group', 'human_resource')
                ->where('key', 'max_negative_leave_minutes')
                ->value('value')
            ?? 0
        );
        return $requestDurationInMin <= ($maxNegativeLeaveMinutes+$remainingLeave-$totalPendingLeaveRequestDurationInMin);


    }

    /**
     * @throws CustomException
     */
    protected function convertRemainingLeaveToMinutes(string $balance): int
    {
        if (!preg_match('/^(\d+),(\d{2}):(\d{2})$/', $balance, $matches)) {
            throw new CustomException('فرمت مانده مرخصی نامعتبر است.');
        }

        $days = (int)$matches[1];
        $hours = (int)$matches[2];
        $minutes = (int)$matches[3];
        return $days * 440 + $hours * 60 + $minutes;
    }

}
