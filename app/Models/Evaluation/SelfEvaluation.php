<?php

namespace App\Models\Evaluation;

use Illuminate\Database\Eloquent\Model;

class SelfEvaluation extends Model
{
    protected $fillable = [
        'evaluation_id',
        'user_id',
        'question_id'
    ];
}
