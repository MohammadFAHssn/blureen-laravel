<?php

namespace App\Services\Evaluation;

use App\Constants\AppConstants;
use App\Models\Evaluation\Evaluation;
use App\Models\Evaluation\SelfEvaluation;
use App\Models\Evaluation\SelfEvaluationAnswer;
use Illuminate\Support\Facades\DB;

class SelfEvaluationService
{
    public function evaluate(array $data)
    {
        $EvaluationId = Evaluation::active()->first()->id;

        $userId = auth()->user()->id;

        $answers = $data['answers'];

        DB::transaction(function () use ($answers, $EvaluationId, $userId) {

            foreach ($answers as $answer) {
                $selfEvaluation = SelfEvaluation::create([
                    'evaluation_id' => $EvaluationId,
                    'user_id' => $userId,
                    'question_id' => $answer['question_id'],
                ]);

                if ($answer['question_type_id'] !== AppConstants::QUESTION_TYPES['MULTIPLE_CHOICE']) {
                    SelfEvaluationAnswer::create([
                        'self_evaluation_id' => $selfEvaluation->id,
                        'score' => $answer['score'] ?? null,
                        'selected_option_id' => $answer['option_id'] ?? null,
                        'answer_text' => $answer['text'] ?? null,
                    ]);
                } else {
                    foreach ($answer['option_ids'] as $optionId) {
                        SelfEvaluationAnswer::create([
                            'self_evaluation_id' => $selfEvaluation->id,
                            'score' => null,
                            'selected_option_id' => $optionId,
                            'answer_text' => null,
                        ]);
                    }
                }
            }
        });
    }
}
