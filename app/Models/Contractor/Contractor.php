<?php

namespace App\Models\Contractor;

use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    protected $table = 'contractors';

    protected $fillable = [
        'first_name',
        'last_name',
        'national_code',
        'mobile_number',
        'active',
        'created_by',
        'edited_by',
    ];
}
