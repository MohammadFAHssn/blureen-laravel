<?php

namespace App\Http\Controllers\Evaluation;

use App\Http\Requests\Evaluation\EvaluateRequest;
use App\Services\Evaluation\EvaluationScoreService;

class EvaluationScoreController
{
    protected $evaluationScoreService;

    public function __construct()
    {
        $this->evaluationScoreService = new EvaluationScoreService();
    }

    public function evaluate(EvaluateRequest $request)
    {
        return response()->json(['data' => $this->evaluationScoreService->evaluate($request->validated())], 200);
    }
}
