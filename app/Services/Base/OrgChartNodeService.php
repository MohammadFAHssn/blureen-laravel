<?php
namespace App\Services\Base;

use App\Models\Base\OrgChartNode;
use App\Models\Base\OrgPosition;
use App\Models\Base\OrgUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrgChartNodeService
{
    public function get()
    {
        return OrgChartNode::with([
            'allPrimaryAndDeputyUsers.avatar',
            'orgPosition',
            'orgUnit',
        ])->get();
    }

    public function getUserOrgChartNodes($data)
    {
        $userId = $data['user_id'];

        return OrgChartNode::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with([
                'users' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                },
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
            $child->push(...$children->users);
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

    public function getLiaisonChild($data)
    {
        $userId = $data['user_id'];

        return User::whereHas('orgChartNodesAsPrimary.orgUnit.liaisonOrgUnits', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->distinct()
            ->get();
    }

    public function getUserSubordinates($data)
    {
        $userId = $data['user_id'];

        $userAndChild = $this->getUserAndChild(['user_id' => $userId]);
        $liaisonChild = $this->getLiaisonChild(['user_id' => $userId]);

        return $userAndChild->merge($liaisonChild)->unique('id')->values();
    }

    public function getUserOrgPositions($userId)
    {
        return OrgChartNode::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
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
        foreach ($this->getUserOrgChartNodes(['user_id' => $userId]) as $userOrgChartNodes) {
            $parentNode = $userOrgChartNodes->parentRecursive;

            while ($parentNode) {
                if ($parentNode->orgPosition->level <= $orgPositionLevel) {
                    $supervisor[] = $parentNode->only(['users', 'orgUnit', 'orgPosition']);
                    break;
                }
                $parentNode = $parentNode->parentRecursive;
            }
        }

        return $supervisor;
    }

    public function update($data)
    {
        $orgChartNode = $data['orgChartNode'];

        DB::transaction(function () use ($orgChartNode) {

            $orgUnit = OrgUnit::firstOrCreate(['name' => $orgChartNode['orgUnit']['name']]);

            $node = OrgChartNode::updateOrCreate(
                [
                    'id' => $orgChartNode['id']
                ],
                [
                    'org_unit_id' => $orgUnit->id,
                    'org_position_id' => $orgChartNode['orgPosition']['id'],
                    'parent_id' => $orgChartNode['parentId'],
                ]
            );

            $node->users()->sync(collect($orgChartNode['users'])->pluck('id')->toArray());
        });

        return ['status' => 'success'];
    }

    public function delete($data)
    {
        OrgChartNode::whereId($data['id'])->delete();

        return ['status' => 'success'];
    }

    public function organize($data)
    {
        $orgChartNodes = $data['orgChartNodes'];

        DB::transaction(function () use ($orgChartNodes) {

            foreach ($orgChartNodes as $orgChartNode) {
                $node = OrgChartNode::whereId($orgChartNode['id']);

                $node->update([
                    'parent_id' => $orgChartNode['parentId'],
                ]);
            }
        });

        return ['status' => 'success'];
    }
}
