<?php

namespace App\Models\Evaluation;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Evaluatee extends Model
{
    public function evaluator()
    {
        return $this->belongsTo(Evaluator::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
