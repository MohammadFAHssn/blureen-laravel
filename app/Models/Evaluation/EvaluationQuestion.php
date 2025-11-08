<?php

namespace App\Models\Evaluation;

use Illuminate\Database\Eloquent\Model;

class EvaluationQuestion extends Model
{
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
