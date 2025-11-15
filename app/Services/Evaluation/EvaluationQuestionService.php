<?php

namespace App\Services\Evaluation;

use App\Models\Evaluation\Evaluation;
use App\Models\Evaluation\SelfEvaluation;
use App\Repositories\Evaluation\EvaluationQuestionRepository;

class EvaluationQuestionService
{
    protected $evaluationQuestionRepository;

    public function __construct()
    {
        $this->evaluationQuestionRepository = new EvaluationQuestionRepository();
    }

    public function getActives()
    {
        return $this->evaluationQuestionRepository->getActives();
    }

    public function getSelfEvaluation()
    {
        $activeEvaluation = Evaluation::active()->first();

        if (!$activeEvaluation) {
            return null;
        }

        $userId = auth()->user()->id;

        $hasUserParticipated = SelfEvaluation::whereUserId($userId)->exists();

        if ($hasUserParticipated) {
            return null;
        }

        return $this->evaluationQuestionRepository->getSelfEvaluation();
    }
}
