<?php

namespace App\Models\Evaluation;

use Illuminate\Database\Eloquent\Model;

class EvaluationQuestionCategory extends Model
{
    public function questions()
    {
        return $this->hasMany(EvaluationQuestion::class, 'category_id');
    }
}
