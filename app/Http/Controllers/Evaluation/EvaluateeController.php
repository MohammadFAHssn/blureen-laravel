<?php

namespace App\Http\Controllers\Evaluation;

use App\Services\Evaluation\EvaluateeService;

class EvaluateeController
{
    protected $evaluateeService;

    public function __construct()
    {
        $this->evaluateeService = new EvaluateeService();
    }
}
