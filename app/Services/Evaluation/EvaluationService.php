<?php

namespace App\Services\Evaluation;

use App\Repositories\Evaluation\EvaluationRepository;

class EvaluationService
{
    protected $evaluationRepository;

    public function __construct()
    {
        $this->evaluationRepository = new EvaluationRepository();
    }
}
