<?php

namespace App\Models\HrRequest;

use App\Models\Base\RequestType;
use App\Models\Base\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrRequest extends Model
{
    protected $fillable = [
        'user_id',
        'request_type_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'status_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requestType(): BelongsTo
    {
        return $this->belongsTo(RequestType::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(HrRequestDetail::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(HrRequestApproval::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

}
