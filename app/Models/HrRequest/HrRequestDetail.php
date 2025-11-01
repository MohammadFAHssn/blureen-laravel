<?php

namespace App\Models\HrRequest;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrRequestDetail extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(HrRequest::class,'hr_request_id');
    }

}
