<?php

namespace App\Models\Evaluation;

use Illuminate\Database\Eloquent\Model;

class EvaluationQuestion extends Model
{
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function evaluationType()
    {
        return $this->belongsTo(EvaluationType::class);
    }

    public function questionType()
    {
        return $this->belongsTo(QuestionType::class);
    }

    public function category()
    {
        return $this->belongsTo(EvaluationQuestionCategory::class, 'category_id');
    }

    public function options()
    {
        return $this->belongsToMany(
            EvaluationQuestionOption::class,
            'evaluation_question_option_links',
            'question_id',
            'option_id'
        );
    }
}
