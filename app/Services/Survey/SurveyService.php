<?php

namespace App\Services\Survey;

use App\Models\Survey\Survey;

class SurveyService
{
    public function create($request)
    {
        Survey::create([
            'title' => $request['title'],
            'porsline_id' => $request['porslineId'],
        ]);
    }

    public function delete($request)
    {
        Survey::whereIn('id', $request['ids'])->delete();
    }
}
