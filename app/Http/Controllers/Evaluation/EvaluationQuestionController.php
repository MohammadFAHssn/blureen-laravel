<?php

namespace App\Http\Controllers\Evaluation;

use App\Services\Evaluation\EvaluationQuestionService;

class EvaluationQuestionController
{
    protected $evaluationQuestionService;

    public function __construct()
    {
        $this->evaluationQuestionService = new EvaluationQuestionService();
    }

    public function getActives()
    {
        return response()->json(['data' => $this->evaluationQuestionService->getActives()], 200);
    }
}
