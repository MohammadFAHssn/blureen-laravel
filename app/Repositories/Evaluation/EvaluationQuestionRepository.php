<?php

namespace App\Repositories\Evaluation;

use App\Models\Evaluation\EvaluationQuestionCategory;

class EvaluationQuestionRepository
{
    public function getActives()
    {
        return EvaluationQuestionCategory::with([
            'questions' => function ($query) {
                $query->active();
            },
        ])->get();
    }
}
