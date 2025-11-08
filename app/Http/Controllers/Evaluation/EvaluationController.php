<?php

namespace App\Http\Controllers\Evaluation;

use App\Services\Evaluation\EvaluationService;

class EvaluationController
{
    protected $evaluationService;

    public function __construct()
    {
        $this->evaluationService = new EvaluationService();
    }
}
