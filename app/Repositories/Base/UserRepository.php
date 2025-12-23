<?php

namespace App\Repositories\Base;

use App\Exceptions\CustomException;
use App\Models\User;
use App\Models\Base\FieldPermission;
use Illuminate\Support\Facades\Schema;

class UserRepository
{
    public function getApprovalFlowsAsRequester($requestTypeId)
    {
        // $user = auth()->user();

        // $isUserSuperAdmin = $user->hasRole('Super Admin');

        // $allowedCostCenters = $user->costCentersAsLiaison();

        return User::select('id', 'first_name', 'last_name', 'personnel_code', 'active')
            // ->when(!$isUserSuperAdmin, function ($query) use ($allowedCostCenters) {
            //     $query->whereHas('profile.costCenter', function ($q) use ($allowedCostCenters) {
            //         $q->whereIn('rayvarz_id', $allowedCostCenters->pluck('cost_center_rayvarz_id'));
            //     });
            // })
            ->with([
                'profile:user_id,workplace_id,work_area_id,cost_center_id,job_position_id',
                'profile.workplace:rayvarz_id,name',
                'profile.workArea:rayvarz_id,name',
                'profile.costCenter:rayvarz_id,name',
                'profile.jobPosition:rayvarz_id,name',
                'approvalFlowsAsRequester' => function ($query) use ($requestTypeId) {
                    $query->where('request_type_id', $requestTypeId)->orderBy('priority');
                },
                'approvalFlowsAsRequester.approverUser:id,first_name,last_name,personnel_code',
                'approvalFlowsAsRequester.approverPosition:rayvarz_id,name',
            ])->get();
    }

    public function getDetails()
    {
        $user = auth()->user();
        $userRoleIds = $user->roles->pluck('id');

        [$viewableUserFields, $userWhereConditions] = $this->resolveFieldPermissionsForModel('App\Models\User', $userRoleIds);

        [$viewableUserProfileFields, $userProfileWhereConditions] = $this->resolveFieldPermissionsForModel('App\Models\Base\UserProfile', $userRoleIds);

        [$viewableRoleFields, $roleWhereConditions] = $this->resolveFieldPermissionsForModel('App\Models\Base\Role', $userRoleIds);

        return User::select($viewableUserFields)->where($userWhereConditions)
            ->with([
                'roles' => function ($query) use ($viewableRoleFields, $roleWhereConditions) {
                    $query->select($viewableRoleFields)->where($roleWhereConditions);
                },
                'profile' => function ($query) use ($viewableUserProfileFields, $userProfileWhereConditions) {
                    $query->select($viewableUserProfileFields)->where($userProfileWhereConditions);
                },
                'profile.educationLevel:rayvarz_id,name',
                'profile.workplace:rayvarz_id,name',
                'profile.workArea:rayvarz_id,name',
                'profile.costCenter:rayvarz_id,name',
                'profile.jobPosition:rayvarz_id,name',
            ])->get();
    }

    function resolveFieldPermissionsForModel($modelClass, $roleIds)
    {
        if (!class_exists($modelClass)) {
            throw new CustomException("Model class not found: {$modelClass}", 500);
        }

        $model = app($modelClass);
        $table = $model->getTable();

        $viewableFields = FieldPermission::where('model_class', $modelClass)
            ->whereIn('role_id', $roleIds)
            ->pluck('field_value', 'field_name')->toArray();

        if (empty($viewableFields)) {
            throw new CustomException("Field permission error: No viewable fields defined for " . $modelClass . " model.", 500);
        }

        $realColumns = Schema::getColumnListing($table);
        foreach ($viewableFields as $fieldName => $fieldValue) {
            if (!in_array($fieldName, $realColumns)) {
                throw new CustomException("Field permission error: The field '{$fieldName}' does not exist in " . $table . " table.", 500);
            }
        }

        $whereConditions = $viewableFields;
        foreach ($viewableFields as $fieldName => $fieldValue) {
            if ($fieldValue === '*') {
                unset($whereConditions[$fieldName]);
            }
        }

        return [array_keys($viewableFields), $whereConditions];
    }
}
