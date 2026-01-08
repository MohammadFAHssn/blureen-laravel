<?php

namespace App\Services\Survey;

use App\Exceptions\CustomException;
use App\Models\Survey\Survey;
use App\Models\Survey\SurveyUrl;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SurveyService
{
    public function create($data)
    {
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => config('services.porsline.authorization'),
            ])
                ->post(
                    config('services.porsline.base_url') . $data['porsline_id'] . '/variables/',
                    [
                        'variables' => [
                            [
                                "name" => "personnel_code",
                                "variable_source" => 1,
                                "type" => 1,
                            ],
                            [
                                "name" => "first_name",
                                "variable_source" => 1,
                                "type" => 1,
                            ],
                            [
                                "name" => "last_name",
                                "variable_source" => 1,
                                "type" => 1,
                            ],
                            [
                                "name" => "gender",
                                "variable_source" => 1,
                                "type" => 1,
                            ],
                            [
                                "name" => "education",
                                "variable_source" => 1,
                                "type" => 1,
                            ],
                            [
                                "name" => "workplace",
                                "variable_source" => 1,
                                "type" => 1,
                            ],
                            [
                                "name" => "work_area",
                                "variable_source" => 1,
                                "type" => 1,
                            ],
                            [
                                "name" => "cost_center",
                                "variable_source" => 1,
                                "type" => 1,
                            ],
                            [
                                "name" => "job_position",
                                "variable_source" => 1,
                                "type" => 1,
                            ],
                        ],
                    ],
                );

            if ($response->failed()) {
                Log::error('Porsline create new variables failed', [
                    'status' => $response->status(),
                    'body' => Str::limit($response->body(), 2000),
                ]);
                throw new CustomException('هنگام ایجاد متغیرهای جدید در Porsline خطایی رخ داده‌است.', 500);
            }

            Survey::create($data);

            return [
                'message' => 'نظرسنجی با موفقیت ایجاد شد.',
            ];
        } catch (\Exception $e) {
            Log::error('Porsline create new variables failed', [
                'error' => $e->getMessage(),
            ]);
            throw new CustomException('هنگام ایجاد متغیرهای جدید در Porsline خطایی رخ داده‌است.', 500);
        }
    }

    public function update($data)
    {
        $survey = Survey::find($data['id']);
        $survey->update($data);
    }

    public function delete($request)
    {
        Survey::whereIn('id', $request['ids'])->delete();
    }

    public function participate($request)
    {

        $user = auth()->user();

        $surveyInProgress = SurveyUrl::where('porsline_id', $request['porslineId'])
            ->where('user_id', $user->id)
            ->first()['url'] ?? null;

        if ($surveyInProgress) {
            return $surveyInProgress;
        } else {
            try {
                $response = Http::withoutVerifying()->withHeaders([
                    'Authorization' => config('services.porsline.authorization'),
                ])
                    ->post(
                        config('services.porsline.base_url') . $request['porslineId'] . '/variables/hashes/',
                        [
                            'values' => [
                                [
                                    "personnel_code" => $user->personnel_code,
                                    "first_name" => $user->first_name,
                                    "last_name" => $user->last_name,
                                    "gender" => $user->profile?->gender,
                                    "education" => 'نامشخص',
                                    "workplace" => $user->profile?->workplace?->name,
                                    "work_area" => $user->profile?->workArea?->name,
                                    "cost_center" => $user->profile?->costCenter?->name,
                                    "job_position" => 'نامشخص',
                                    "is_unique" => true,
                                ],
                            ],
                        ],
                    );

                if ($response->failed()) {
                    Log::error('Porsline create url failed', [
                        'status' => $response->status(),
                        'body' => Str::limit($response->body(), 2000),
                    ]);
                    throw new CustomException('هنگام ایجاد url در Porsline خطایی رخ داده‌است.', 500);
                }

                $url = $response->json()['urls'][0];

                SurveyUrl::create([
                    'porsline_id' => $request['porslineId'],
                    'user_id' => $user->id,
                    'url' => $url,
                ]);

                return $url;
            } catch (\Exception $e) {
                Log::error('Porsline create url failed', [
                    'error' => $e->getMessage(),
                ]);
                throw new CustomException('هنگام ایجاد url در Porsline خطایی رخ داده‌است.', 500);
            }

        }
    }
}
