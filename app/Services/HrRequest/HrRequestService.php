<?php

namespace App\Services\HrRequest;


use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Base\Setting;
use Illuminate\Support\Collection;
use Morilog\Jalali\Jalalian;
use App\Constants\AppConstants;
use App\Services\Api\KasraService;
use App\Exceptions\CustomException;
use App\Models\HrRequest\HrRequest;
use App\Services\Base\OrgChartNodeService;
use Illuminate\Http\Client\ConnectionException;
use App\Services\Base\RequestApprovalRuleService;
use App\Repositories\HrRequest\HrRequestRepository;
use App\Repositories\HrRequest\HrRequestDetailRepository;
use function PHPUnit\Framework\matches;

class HrRequestService
{
    protected HrRequestRepository $hrRequestRepository;
    protected HrRequestDetailRepository $hrRequestDetailRepository;
    protected HrRequestApprovalService $hrRequestApprovalService;
    protected KasraService $kasraService;
    protected OrgChartNodeService $orgChartNodeService;
    protected RequestApprovalRuleService $requestApprovalRuleService;


    public function __construct()
    {
        $this->hrRequestRepository = new HrRequestRepository();
        $this->hrRequestDetailRepository = new HrRequestDetailRepository();
        $this->hrRequestApprovalService = new HrRequestApprovalService();
        $this->kasraService = new KasraService();
        $this->orgChartNodeService = new OrgChartNodeService();
        $this->requestApprovalRuleService = new RequestApprovalRuleService();
    }

    /**
     * @throws CustomException
     * @throws Exception
     */
    public function create(array $data)
    {
        $formTypeId = $data['request_type_id'];

        return match ($formTypeId) {
            AppConstants::HR_REQUEST_TYPES['DAILY_LEAVE'] => $this->createDailyLeaveRequest($data),
            AppConstants::HR_REQUEST_TYPES['HOURLY_LEAVE'] => $this->createHourlyLeaveRequest($data),
            AppConstants::HR_REQUEST_TYPES['OVERTIME'] => $this->createOvertimeRequest($data),
            AppConstants::HR_REQUEST_TYPES['SICK'] => $this->createSickRequest($data),
            default => throw new CustomException('فرم ارسالی نامعتبر است', 400),
        };
    }

    /**
     * @throws CustomException
     * @throws ConnectionException
     * @throws Exception
     */
    public function update(array $data)
    {
        $hrRequest = HrRequest::find($data['requestId']);

        if ($hrRequest->user_id == auth()->user()->id) {
            $requestApprovals = $hrRequest->approvals;
            foreach ($requestApprovals as $approval){
                if($approval->status_id != AppConstants::HR_REQUEST_PENDING_STATUS)
                    throw new CustomException('امکان ویرایش درخواست های بررسی شده وجود ندارد.');
            }
        }

        return match ($hrRequest->request_type_id) {
            AppConstants::HR_REQUEST_TYPES['DAILY_LEAVE'] => $this->updateDailyLeaveRequest($data),
        };

    }

    /**
     * @throws Exception
     */
    public function getApprovers(array $data)
    {
        $hrRequest = HrRequest::find($data['requestId']);
        if ($hrRequest->status_id != AppConstants::HR_REQUEST_PENDING_STATUS) {
            throw new CustomException('امکان ارجاع درخواست های تایید/رد شده وجود ندارد.');
        }

        return $this->getApprovalFlowForDailyRequest($data['user_id'],$data['user_id']);
    }

    public function referral(array $data): true
    {
        return $this->hrRequestApprovalService->create($data['requestId'],$data['approver']);
    }

    /**
     * @throws Exception
     */
    public function delete($data)
    {
        $hrRequest = HrRequest::find($data['requestId']);
        if ($hrRequest->user_id == auth()->user()->id) {
            $requestApprovals = $hrRequest->approvals;
            foreach ($requestApprovals as $approval){
                if($approval->status_id != AppConstants::HR_REQUEST_PENDING_STATUS)
                    throw new CustomException('امکان حذف درخواست های بررسی شده وجود ندارد.');
            }
        }

        if (!$hrRequest) {
            throw new CustomException('درخواست مورد نظر یافت نشد.', 404);
        }

        $hasProcessedApproval = $hrRequest->approvals()
            ->where('status_id', '!=', AppConstants::HR_REQUEST_PENDING_STATUS)
            ->exists();

        if ($hasProcessedApproval) {
            throw new CustomException(
                'امکان حذف درخواست بررسی شده وجود ندارد.',
                403
            );
        }

        return $hrRequest->delete();
    }

    /**
     * @throws CustomException
     * @throws ConnectionException
     * @throws Exception
     */
    protected function createDailyLeaveRequest(array $data)
    {
        $startDate = Jalalian::fromFormat('Y-m-d', $data['start_date'])->toCarbon()->startOfDay();
        $endDate = Jalalian::fromFormat('Y-m-d', $data['end_date'])->toCarbon()->startOfDay();
        $leaveRequestDuration = ($startDate->diffInDays($endDate) + 1) * AppConstants::WORK_DAY_MINUTES;
        $data['details'] = [
            'duration' => $leaveRequestDuration
        ];

        if (!$this->userHasEnoughLeaveBalance($data['user_id'], $leaveRequestDuration)) {
            throw new CustomException('مانده مرخصی کافی نمیباشد.', 403);
        }

        $approvalFlows = $this->getApprovalFlowForDailyRequest($data['user_id'], auth()->user()->id);

        $hrRequest = $this->hrRequestRepository->create($data);
        $this->hrRequestApprovalService->create($hrRequest->id, $approvalFlows);
        return true;
    }

    /**
     * @throws Exception
     */

    protected function createHourlyLeaveRequest(array $data)
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
            throw new CustomException('مجموع مرخصی ساعتی در یک روز نمی‌تواند بیشتر از ۳ ساعت و ۳۰ دقیقه باشد.', 403);
        }

        if (!$this->userHasEnoughLeaveBalance($data['user_id'], $currentRequestDuration)) {
            throw new CustomException('مانده مرخصی کافی نمیباشد.', 403);
        }


        $hrRequest = $this->hrRequestRepository->create($data);
        //$this->hrRequestApprovalService->create($hrRequest->id);

        return true;
    }

    protected function createOvertimeRequest(array $data): true
    {
        $startInCarbon = Carbon::createFromFormat('H:i', $data['start_time']);
        $endInCarbon = Carbon::createFromFormat('H:i', $data['end_time']);
        if ($endInCarbon->lt($startInCarbon)) {
            $data['end_date'] = Jalalian::fromFormat('Y-m-d', $data['start_date'])
                ->addDay()->format('Y-m-d');
        }
        $hrRequest = $this->hrRequestRepository->create($data);
        $this->hrRequestApprovalService->create($hrRequest->id);
        return true;
    }

    protected function createSickRequest(array $data): true
    {
        $hrRequest = $this->hrRequestRepository->create($data);
        $this->hrRequestApprovalService->create($hrRequest->id);
        return true;
    }


    public function getUserRequestsOfCurrentMonth($data): Collection
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
            ->with('status', 'approvals.approver')
            ->get();
    }


    /**
     * @throws CustomException
     * @throws ConnectionException
     * @throws Exception
     */
    protected function updateDailyLeaveRequest(array $data): bool
    {
        $hrRequest = HrRequest::find($data['requestId']);
        if ($hrRequest->status_id !== AppConstants::HR_REQUEST_PENDING_STATUS) {
            throw new CustomException('فقط درخواست‌های در روند قابل ویرایش هستند.', 403);
        }

        $startDateCarbon = Jalalian::fromFormat('Y-m-d', $data['start_date'])->toCarbon()->startOfDay();
        $endDateCarbon = Jalalian::fromFormat('Y-m-d', $data['end_date'])->toCarbon()->startOfDay();

        if ($endDateCarbon->lt($startDateCarbon)) {
            throw new CustomException('تاریخ پایان نمی‌تواند قبل از تاریخ شروع باشد.', 422);
        }

        $newDuration = ($startDateCarbon->diffInDays($endDateCarbon) + 1) * AppConstants::WORK_DAY_MINUTES;

        $oldDuration = (int)$hrRequest->details()
            ->where('key', 'duration')
            ->value('value');

        $delta = $newDuration - $oldDuration;

        if ($delta > 0) {
            if (!$this->userHasEnoughLeaveBalance($hrRequest->user_id, $delta)) {
                throw new CustomException('مانده مرخصی کافی نمیباشد.', 403);
            }
        }

        $this->hrRequestRepository->update([
            'id' => $hrRequest->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);

        $hrRequest->details()->update(
            ['key' => 'duration'],
            ['value' => $newDuration]
        );

        return true;
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
        $maxNegativeLeaveMinutes = (int)(
            Setting::query()
                ->where('group', 'human_resource')
                ->where('key', 'max_negative_leave_minutes')
                ->value('value')
            ?? 0
        );
        return $requestDurationInMin <= ($maxNegativeLeaveMinutes + $remainingLeave - $totalPendingLeaveRequestDurationInMin);


    }

    /**
     * @throws CustomException
     */
    protected function convertRemainingLeaveToMinutes(string $balance): int
    {
        if (!preg_match('/^(-?)(\d+),(0\d|1\d|2[0-3]):([0-5]\d)$/', $balance, $matches)) {
            throw new CustomException('فرمت مانده مرخصی نامعتبر است.');
        }

        $sign    = $matches[1] === '-' ? -1 : 1;
        $days    = (int) $matches[2];
        $hours   = (int) $matches[3];
        $minutes = (int) $matches[4];

        $totalMinutes = ($days * 440) + ($hours * 60) + $minutes;

        return $sign * $totalMinutes;
    }


    /**
     * @throws CustomException
     * @throws Exception
     */
    private function getApprovalFlowForDailyRequest($requesterUserId, $loginUserId)
    {
        $approvalFlowForRequest = $this->requestApprovalRuleService->getApprovalFlowForRequest([
            'user_id' => $requesterUserId,
            'request_type_id' => AppConstants::HR_REQUEST_TYPES['DAILY_LEAVE'],
        ]);

        $loginUserOrgPositions = $this->orgChartNodeService->getUserOrgPositions($loginUserId);

        $approvalFlowForRequest = $loginUserOrgPositions->isEmpty()
            ?collect()
            :collect(
                array_filter($approvalFlowForRequest->toArray(), function ($i) use ($loginUserOrgPositions) {
                    return $i['orgPosition']['level'] < $loginUserOrgPositions[0]['level'];
                })
            )->filter()->values();

        $liaison = User::whereId($requesterUserId)
            ->with('orgChartNodesAsPrimary.orgUnit.liaisons')->first()
            ->orgChartNodesAsPrimary->first()
            ->orgUnit->liaisons->first()
            ?->only(['id', 'first_name', 'last_name', 'personnel_code']);

        if (!$liaison)
            throw new CustomException('رابط اداری برای این واحد تعیین نشده است.');

        $approvalFlowForRequest->push(['users' => [$liaison]]);

        return $approvalFlowForRequest;
    }

}
