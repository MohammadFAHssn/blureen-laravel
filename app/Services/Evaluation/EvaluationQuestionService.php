<?php

namespace App\Services\Evaluation;

use App\Repositories\Evaluation\EvaluationQuestionRepository;

class EvaluationQuestionService
{
    protected $evaluationQuestionRepository;

    public function __construct()
    {
        $this->evaluationQuestionRepository = new EvaluationQuestionRepository();
    }

    public function getActives()
    {
        return $this->evaluationQuestionRepository->getActives();
    }
}
