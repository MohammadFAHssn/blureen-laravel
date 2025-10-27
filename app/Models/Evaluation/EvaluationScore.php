<?php

namespace App\Models\Evaluation;

use Illuminate\Database\Eloquent\Model;

class EvaluationScore extends Model
{
    protected $fillable = [
        'evaluatee_id',
        'question_id',
        'score',
    ];
}
