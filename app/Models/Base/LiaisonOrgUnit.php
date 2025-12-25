<?php

namespace App\Models\Base;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LiaisonOrgUnit extends Model
{
    protected $table = 'liaisons_org_units';

    public function liaisonUsers()
    {
        return $this->hasManyThrough(
            User::class,
            OrgChartNode::class,
            'org_unit_id',  // Foreign key on org_chart_nodes
            'id',           // Foreign key on users
            'org_unit_id',  // Local key on liaisons_org_units
            'user_id'       // Local key on org_chart_nodes
        );
    }
}
