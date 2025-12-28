<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;

class OrgUnit extends Model
{
    protected $fillable = [
        'name',
    ];

    public function liaisonOrgUnits()
    {
        return $this->hasMany(LiaisonOrgUnit::class);
    }
}
