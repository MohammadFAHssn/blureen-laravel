<?php

namespace App\Models\Evaluation;

use Illuminate\Database\Eloquent\Model;

class SelfEvaluationAnswer extends Model
{
    protected $fillable = [
        'self_evaluation_id',
        'score',
        'selected_option_id',
        'answer_text'
    ];
}
