<?php
namespace App\Repositories\Evaluation;

use App\Models\Evaluation\Evaluatee;
use App\Models\Evaluation\EvaluationQuestion;
use App\Models\Evaluation\EvaluationScore;

class EvaluationScoreRepository
{
    public function evaluate($data)
    {
        $evaluateeId = $data['results'][0]['evaluatee_id'];

        $totalScoreOfEvaluatee = 0;
        foreach ($data['results'] as $questionScore) {
            EvaluationScore::updateOrCreate(
                [
                    'evaluatee_id' => $evaluateeId,
                    'question_id' => $questionScore['question_id'],
                ],
                [
                    'score' => $questionScore['score'],
                ]
            );

            $totalScoreOfEvaluatee += $questionScore['score'];
        }

        $questionCount = EvaluationQuestion::active()->count();
        $totalScore = $questionCount * 10;

        $evaluatee = Evaluatee::find($evaluateeId);
        $evaluatee->update(['final_score' => round($totalScoreOfEvaluatee * 20 / $totalScore, 2)]);
    }
}
