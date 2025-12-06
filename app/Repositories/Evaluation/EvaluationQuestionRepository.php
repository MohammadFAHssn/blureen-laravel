<?php

namespace App\Repositories\Evaluation;

use App\Models\Evaluation\EvaluationQuestion;
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

    public function getSelfEvaluation()
    {
        return EvaluationQuestion::whereHas('evaluationType', function ($query) {
            $query->whereName('self evaluation');
        })->active()->with('questionType', 'category', 'options')->get();
    }
}
