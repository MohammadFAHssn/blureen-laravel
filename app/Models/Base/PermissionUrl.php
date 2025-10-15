<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;

class PermissionUrl extends Model
{
    protected $table = 'permissions_urls';

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
