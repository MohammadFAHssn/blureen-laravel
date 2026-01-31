<?php
namespace App\Repositories\Base;

use App\Models\Base\OrgUnit;

class OrgUnitRepository
{
    public function getUserOrgUnits($userId)
    {
        return OrgUnit::whereHas('orgChartNodeUsers', function ($query) use ($userId) {
            $query
                ->where('user_id', $userId)
                ->where('role', 'primary');
        })
            ->distinct()
            ->get();
    }
}
