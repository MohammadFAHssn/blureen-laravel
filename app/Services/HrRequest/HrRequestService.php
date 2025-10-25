<?php

namespace App\Services\HrRequest;


use App\Constants\AppConstants;
use App\Exceptions\CustomException;
use App\Models\HrRequest\HrRequest;
use App\Models\User;
use App\Repositories\HrRequest\HrRequestRepository;
use App\Services\Base\ApprovalFlowService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;
use Termwind\Components\Hr;


class HrRequestService
{
    protected HrRequestRepository $hrRequestRepository;
    protected ApprovalFlowService $approvalFlowService;
    protected HrRequestApprovalService $hrRequestApprovalService;

    public function __construct(HrRequestRepository $hrRequestRepository, ApprovalFlowService $approvalFlowService, HrRequestApprovalService $hrRequestApprovalService)
    {
        $this->hrRequestRepository = $hrRequestRepository;
        $this->approvalFlowService = $approvalFlowService;
        $this->hrRequestApprovalService = $hrRequestApprovalService;
    }

    /**
     * @throws CustomException
     * @throws Exception
     */
    public function create(array $data): true
    {
        $formTypeId = $data['request_type_id'];
        $userId = $data['user_id'];

        $userApprovalFlows = $this->approvalFlowService->getUserApprovalFlow($userId, $formTypeId);
        if ($userApprovalFlows->isEmpty()) {
            throw new CustomException('برای این درخواست رده تاییدیه تعریف نشده است.', 403);
        }


        return match ($formTypeId) {
            AppConstants::HR_REQUEST_TYPE_DAILY_LEAVE => $this->createDailyLeaveRequest($data, $userApprovalFlows),
            AppConstants::HR_REQUEST_TYPE_HOURLY_LEAVE => $this->createHourlyLeaveRequest($data, $userApprovalFlows),
            AppConstants::HR_REQUEST_TYPE_OVERTIME => $this->createOvertimeRequest($data, $userApprovalFlows),
            AppConstants::HR_REQUEST_TYPE_SICK => $this->createSickRequest($data, $userApprovalFlows),
            default => throw new Exception('فرم ارسالی نامعتبر است', 400),
        };
    }

    protected function createDailyLeaveRequest(array $data, Collection $userApprovalFlows): true
    {
        $hrRequest = $this->hrRequestRepository->create($data);
        $this->hrRequestApprovalService->create($hrRequest->id, $userApprovalFlows);
        return true;
    }

    /**
     * @throws Exception
     */
    protected function createHourlyLeaveRequest(array $data, Collection $userApprovalFlows): true
    {

        $startInCarbon = Carbon::createFromFormat('H:i', $data['start_time']);
        $endInCarbon = Carbon::createFromFormat('H:i', $data['end_time']);


        $totalHourlyLeavesOfDay = 0;
        if ($endInCarbon->lt($startInCarbon)) {
            $totalHourlyLeavesOfDay += $startInCarbon->diffInMinutes($startInCarbon->copy()->endOfDay());
            $totalHourlyLeavesOfDay += Carbon::createFromTime(0, 0)->diffInMinutes($endInCarbon);
            $data['end_date'] = Jalalian::fromFormat('Y/m/d', $data['start_date'])
                ->addDay()->format('Y/m/d');
        } else {
            $totalHourlyLeavesOfDay += $startInCarbon->diffInMinutes($endInCarbon);
        }

        $existingRequests = $this->hrRequestRepository->getUserHourlyLeaveRequestsOfDay($data['user_id'], $data['start_date']);
        foreach ($existingRequests as $request) {
            $startInCarbon = Carbon::createFromFormat('H:i:s', $request['start_time']);
            $endInCarbon = Carbon::createFromFormat('H:i:s', $request['end_time']);
            if ($endInCarbon->lt($startInCarbon)) {
                $totalHourlyLeavesOfDay += $startInCarbon->diffInMinutes($startInCarbon->copy()->endOfDay());
                $totalHourlyLeavesOfDay += Carbon::createFromTime(0, 0)->diffInMinutes($endInCarbon);

                $data['end_date'] = Jalalian::fromFormat('Y/m/d', $data['start_date'])
                    ->addDay()->format('Y/m/d');
            } else {
                $totalHourlyLeavesOfDay += $startInCarbon->diffInMinutes($endInCarbon);
            }
        }

        if ($totalHourlyLeavesOfDay > AppConstants::MAX_HOURLY_LEAVE_MINUTES) {
            throw new Exception('مجموع مرخصی ساعتی در یک روز نمی‌تواند بیشتر از ۳ ساعت و ۳۰ دقیقه باشد.', 403);
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
            $data['end_date'] = Jalalian::fromFormat('Y/m/d', $data['start_date'])
                ->addDay()->format('Y/m/d');
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
            'user_id' => auth()->id(),
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
}
