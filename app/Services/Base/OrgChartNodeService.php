<?php
namespace App\Services\Base;

use App\Models\Base\OrgChartNode;
use App\Models\Base\OrgPosition;

class OrgChartNodeService
{
    public function get()
    {
        $nodes = OrgChartNode::with([
            'user:id,first_name,last_name,personnel_code',
            'orgPosition',
            'orgUnit',
        ])->get();

        return $nodes->groupBy(function ($node) {
            return $node->org_position_id . '-' . $node->org_unit_id . '-' . $node->parent_id;
        })->map(function ($group) {
            $first = $group->first();
            $first->users = $group->pluck('user')->filter()->values();
            unset($first->user);
            unset($first->user_id);
            return $first;
        })->values();
    }

    public function getUserOrgChartNodes($userId)
    {
        return OrgChartNode::where('user_id', $userId)
            ->with([
                'user:id,first_name,last_name,personnel_code',
                'childrenRecursive',
                'parentRecursive',
                'orgPosition',
                'orgUnit',
            ])
            ->get();
    }

    public function getUserOrgPositions($userId)
    {
        return OrgChartNode::where('user_id', $userId)
            ->with('orgPosition')
            ->get()
            ->pluck('orgPosition')
            ->filter()
            ->values();
    }

    public function getUserSupervisor($userId, $orgPositionId)
    {
        $orgPositionLevel = OrgPosition::find($orgPositionId)->level;

        $supervisor = [];
        foreach ($this->getUserOrgChartNodes($userId) as $userOrgChartNodes) {
            $parentNode = $userOrgChartNodes->parentRecursive;

            while ($parentNode) {
                if ($parentNode->orgPosition->level <= $orgPositionLevel) {
                    $supervisor[] = $parentNode->only(['user', 'orgUnit', 'orgPosition']);
                    break;
                }
                $parentNode = $parentNode->parentRecursive;
            }
        }

        return $supervisor;
    }
}
