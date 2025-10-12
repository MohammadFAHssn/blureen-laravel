<?php

namespace App\Http\Controllers\Survey;

use App\Services\Survey\SurveyService;
use App\Http\Requests\Survey\CreateSurveyRequest;
use App\Http\Requests\Survey\UpdateSurveyRequest;
use App\Http\Requests\Survey\DeleteSurveysRequest;
use App\Http\Requests\Survey\ParticipateInSurveyRequest;

class SurveyController
{
    protected $surveyService;

    public function __construct()
    {
        $this->surveyService = new SurveyService();
    }

    public function create(CreateSurveyRequest $request)
    {
        return response()->json(['data' => $this->surveyService->create($request->validated())], 200);
    }

    public function update(UpdateSurveyRequest $request)
    {
        return response()->json(['data' => $this->surveyService->update($request->validated())], 200);
    }

    public function delete(DeleteSurveysRequest $request)
    {
        return response()->json(['data' => $this->surveyService->delete($request)], 200);
    }

    public function participate(ParticipateInSurveyRequest $request)
    {
        return response()->json(['data' => $this->surveyService->participate($request)], 200);
    }
}
