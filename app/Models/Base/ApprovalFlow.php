<?php

namespace App\Models\Base;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ApprovalFlow extends Model
{
    public function requesterUser()
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function requesterPosition()
    {
        return $this->belongsTo(JobPosition::class, 'requester_position_id', 'rayvarz_id');
    }

    public function approverUser()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function approverPosition()
    {
        return $this->belongsTo(JobPosition::class, 'approver_position_id', 'rayvarz_id');
    }
}
