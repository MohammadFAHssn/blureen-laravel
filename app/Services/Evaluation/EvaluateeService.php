<?php

namespace App\Services\Evaluation;

use App\Repositories\Evaluation\EvaluateeRepository;

class EvaluateeService
{
    protected $evaluateeRepository;

    public function __construct()
    {
        $this->evaluateeRepository = new EvaluateeRepository();
    }

    public function getByEvaluator()
    {
        return $this->evaluateeRepository->getByEvaluator();
    }
}
