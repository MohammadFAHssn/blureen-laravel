<?php
namespace App\Repositories\Evaluation;

use App\Exports\Evaluation\EvaluationResultsExport;
use App\Models\Evaluation\Evaluatee;
use App\Models\Evaluation\EvaluationQuestion;
use App\Models\Evaluation\EvaluationScore;
use Maatwebsite\Excel\Facades\Excel;

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

    public function getResults()
    {
        $month = 8;
        $year = 1404;

        // $evaluatees = Evaluatee::whereHas('evaluator.evaluation', function ($query) use ($month, $year) {
        //     $query->where('month', $month)->where('year', $year);
        // })->select('id', 'user_id', 'evaluator_id')->with([
        //             'user:id,personnel_code,first_name,last_name',
        //             'user.profile:id,user_id,cost_center_id',
        //             'user.profile.costCenter:name,rayvarz_id',
        //             'evaluator.user:id,personnel_code,first_name,last_name',
        //             'evaluator.user.profile:id,user_id,cost_center_id',
        //             'evaluator.user.profile.costCenter:name,rayvarz_id',
        //             'scores.question:id,category_id',
        //         ])->get();

        // return $evaluatees;

        return Excel::download(new EvaluationResultsExport($month, $year), 'evaluation-results-' . $month . '-' . $year . '.xlsx');
    }
}
