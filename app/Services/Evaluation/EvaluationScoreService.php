<?php

namespace App\Services\Evaluation;

use App\Repositories\Evaluation\EvaluationScoreRepository;

class EvaluationScoreService
{
    protected $evaluationScoreRepository;

    public function __construct()
    {
        $this->evaluationScoreRepository = new EvaluationScoreRepository();
    }

    public function evaluate($data)
    {
        return $this->evaluationScoreRepository->evaluate($data);
    }
}
