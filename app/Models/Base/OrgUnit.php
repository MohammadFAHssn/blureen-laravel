<?php

namespace App\Models\Base;

use App\Models\User;
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

    public function liaisons()
    {
        return $this->belongsToMany(
            User::class,
            'liaisons_org_units',
            'org_unit_id',
            'user_id'
        );
    }
}
