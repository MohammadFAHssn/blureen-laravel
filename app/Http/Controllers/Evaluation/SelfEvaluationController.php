<?php

namespace App\Http\Controllers\Evaluation;

use App\Services\Evaluation\SelfEvaluationService;
use App\Http\Requests\Evaluation\SelfEvaluateRequest;

class SelfEvaluationController
{
    protected $selfEvaluationService;

    public function __construct()
    {
        $this->selfEvaluationService = new SelfEvaluationService();
    }

    public function evaluate(SelfEvaluateRequest $request)
    {
        return response()->json(['data' => $this->selfEvaluationService->evaluate($request->validated())], 200);
    }
}
