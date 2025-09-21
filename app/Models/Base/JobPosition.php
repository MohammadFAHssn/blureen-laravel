<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    protected $primaryKey = 'rayvarz_id';

    public function approvalFlowsAsRequester()
    {
        return $this->hasMany(ApprovalFlow::class, 'requester_position_id', 'rayvarz_id');
    }
}
