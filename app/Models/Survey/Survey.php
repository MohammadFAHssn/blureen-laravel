<?php

namespace App\Models\Survey;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $fillable = ['title', 'porsline_id', 'active'];
}
