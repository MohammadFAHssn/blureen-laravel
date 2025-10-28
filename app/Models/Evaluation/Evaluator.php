<?php

namespace App\Models\Evaluation;

use Illuminate\Database\Eloquent\Model;

class Evaluator extends Model
{
    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }
}
