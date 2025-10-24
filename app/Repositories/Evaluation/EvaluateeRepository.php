<?php

namespace App\Repositories\Evaluation;

use App\Models\Evaluation\Evaluatee;

class EvaluateeRepository
{
    public function getByEvaluator()
    {
        $userId = auth()->user()->id;

        return Evaluatee::whereHas('evaluator', function ($query) use ($userId) {
            $query->whereHas('evaluation', function ($query) {
                $query->where('active', true);
            })->where('user_id', $userId);
        })->with('user:id,first_name,last_name,personnel_code')->get();
    }
}
