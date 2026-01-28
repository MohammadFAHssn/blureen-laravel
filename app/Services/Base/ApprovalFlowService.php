<?php

namespace App\Services\Base;

use App\Exceptions\CustomException;
use App\Models\Base\ApprovalFlow;
use App\Models\Base\CostCenter;
use App\Models\Base\JobPosition;
use App\Models\Base\UserProfile;
use App\Models\User;
use App\Repositories\Base\ApprovalFlowRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ApprovalFlowService
{
    protected ApprovalFlowRepository $approvalFlowRepository;

    public function __construct()
    {
        $this->approvalFlowRepository = new ApprovalFlowRepository();
    }
    public function update($request): void
    {
        $approvalFlows = [];
        foreach ($request['approvalFlows'] as $approval) {

            $approvalDeleted = empty($approval['approver_user_id']) && empty($approval['approver_position_id']) && empty($approval['approver_center_id']);

            if (isset($approval['requester_user_id'])) {

                ApprovalFlow::where('requester_user_id', $approval['requester_user_id'])
                    ->where('request_type_id', $approval['request_type_id'])
                    ->delete();

                if ($approvalDeleted) {
                    continue;
                }

                $approvalFlows[] = $approval;
            } elseif (isset($approval['requester_position_id']) && isset($approval['requester_center_id'])) {

                ApprovalFlow::where('requester_position_id', $approval['requester_position_id'])
                    ->where('requester_center_id', $approval['requester_center_id'])
                    ->where('request_type_id', $approval['request_type_id'])
                    ->delete();

                if ($approvalDeleted) {
                    continue;
                }

                $approvalFlows[] = $approval;
            }
        }

        foreach ($approvalFlows as $approval) {
            ApprovalFlow::create($approval);
        }
    }

    public function getUserApprovalFlow(int $userId, int $requestTypeId): Collection
    {
        $flows = ApprovalFlow::where([
            'requester_user_id' => $userId,
            'request_type_id'   => $requestTypeId,
        ])->get();

        if ($flows->isNotEmpty()) {
            return $flows;
        }

        $positionId = UserProfile::where('user_id', $userId)->value('job_position_id');

        if ($positionId) {
            return ApprovalFlow::where([
                'requester_position_id' => $positionId,
                'request_type_id'       => $requestTypeId,
            ])->get();
        }

        return new Collection();
    }

    /**
     * @throws CustomException
     */
    function getRequestersForCurrentApprover($data)
    {
        $requestTypeId = $data['request_type_id'];
        $user = User::find(auth()->id());
        if(!$user)
            throw new CustomException('کاربر وارد نشده',401);
        $approverId = $user->id;


        $me = User::query()
            ->select('id')
            ->with(['profile:user_id,job_position_id,cost_center_id'])
            ->findOrFail($approverId);

        $myPos = optional($me->profile)->job_position_id;
        $myCtr = optional($me->profile)->cost_center_id;

        return User::query()
            ->select(['users.id','users.personnel_code','users.first_name','users.last_name'])
            ->leftJoin('user_profiles as p', 'p.user_id', '=', 'users.id')
            ->where('users.active', 1)
            ->whereExists(function ($af) use ($requestTypeId, $approverId, $myPos, $myCtr) {
                $af->select(DB::raw(1))
                    ->from('approval_flows as af')
                    ->where('af.request_type_id', $requestTypeId)
                    ->where(function ($a) use ($approverId, $myPos, $myCtr) {
                        $a->where('af.approver_user_id', $approverId);
                        if ($myPos && $myCtr) {
                            $a->orWhere(function ($aa) use ($myPos, $myCtr) {
                                $aa->where('af.approver_position_id', $myPos)
                                    ->where('af.approver_center_id',   $myCtr);
                            });
                        }
                    })
                    ->where(function ($r) {
                        $r->whereColumn('af.requester_user_id', 'users.id')
                            ->orWhere(function ($rr) {
                                $rr->whereNull('af.requester_user_id')
                                    ->whereNotNull('af.requester_position_id')
                                    ->whereNotNull('af.requester_center_id')
                                    ->whereColumn('af.requester_position_id', 'p.job_position_id')
                                    ->whereColumn('af.requester_center_id',   'p.cost_center_id');
                            });
                    });
            })
            ->orderBy('users.id')
            ->get();
    }
}
