<?php
namespace App\Services\Base;

use App\Models\User;
use App\Models\Base\OrgPosition;
use App\Models\Base\OrgChartNode;

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

    public function getUserOrgChartNodes($data)
    {
        $userId = $data['user_id'];

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

    public function getUserChild($data)
    {
        $userId = $data['user_id'];
        $userOrgChartNodes = $this->getUserOrgChartNodes(['user_id' => $userId]);

        if ($userOrgChartNodes->isEmpty()) {
            return collect();
        }

        $child = collect();
        foreach ($userOrgChartNodes as $node) {
            $child = $child->merge($this->getUserChildRecursive($node));
        }

        return $child->unique('id')->values();
    }

    private function getUserChildRecursive($userOrgChartNode)
    {
        $child = collect();

        foreach ($userOrgChartNode->childrenRecursive as $children) {
            $child->push($children->user);
            $child = $child->merge($this->getUserChildRecursive($children));
        }

        return $child;
    }

    public function getUserAndChild($data)
    {
        $userId = $data['user_id'];
        $user = User::where('id', $userId)->select('id', 'first_name', 'last_name', 'personnel_code')->first();

        $userChild = $this->getUserChild(['user_id' => $userId]);

        return $userChild->prepend($user)->values();
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
