<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;

class RequestApprovalRule extends Model
{
    public function approverOrgChartNode()
    {
        return $this->belongsTo(OrgChartNode::class);
    }
}
