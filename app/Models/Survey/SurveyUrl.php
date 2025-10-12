<?php

namespace App\Models\Survey;

use Illuminate\Database\Eloquent\Model;

class SurveyUrl extends Model
{
    protected $fillable = [
        'porsline_id',
        'user_id',
        'url',
    ];
}
