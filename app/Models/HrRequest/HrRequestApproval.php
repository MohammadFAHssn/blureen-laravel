<?php

namespace App\Models\HrRequest;

use App\Models\Base\ApprovalFlow;
use App\Models\Base\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrRequestApproval extends Model
{
    protected $fillable = [
        'hr_request_id',
        'approver_user_id',
        'priority',
        'status_id'
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(HrRequest::class,'hr_request_id');
    }

    public function approvalFlow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

}
