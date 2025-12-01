<?php

namespace App\Services\Base;

use App\Models\Base\OrgChartNode;
use App\Models\Base\OrgPosition;

class OrgChartNodeService
{
    public function getUserOrgChartNodes($userId)
    {
        return OrgChartNode::where('user_id', $userId)
            ->with([
                'user:id,first_name,last_name,personnel_code',
                'childrenRecursive',
                'parentRecursive',
                'orgPosition',
                'orgUnit'
            ])
            ->get();
    }

    public function getUserOrgPosition($userId)
    {
        return OrgChartNode::where('user_id', $userId)
            ->with('orgPosition')->get()->pluck('orgPosition')
            ->filter()->values()->toArray();
    }

    public function getUserSupervisor($userId, $orgPositionId)
    {
        $orgPositionLevel = OrgPosition::find($orgPositionId)->level;

        $parentNode = $this->getUserOrgChartNodes($userId)[0]->parentRecursive;

        $supervisor = null;

        while ($parentNode) {
            if ($parentNode->orgPosition->level <= $orgPositionLevel) {
                $supervisor = $parentNode->only(['user', 'orgUnit', 'orgPosition']);
                break;
            }
            $parentNode = $parentNode->parentRecursive;
        }
        return $supervisor;
    }
}
