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

    public function getByEvaluator()
    {
        return response()->json(['data' => $this->evaluateeService->getByEvaluator()], 200);
    }
}
