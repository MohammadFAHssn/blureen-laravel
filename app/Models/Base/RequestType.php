<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class RequestType extends Model
{
    public function approvalFlows(): HasMany
    {
        return $this->hasMany(ApprovalFlow::class);
    }
}
