<?php

namespace App\Services\Survey;

use App\Exceptions\CustomException;
use App\Models\Survey\Survey;
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
                    config('services.porsline.create_new_variables') . $data['porsline_id'] . '/variables/',
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
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 2000),
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
        return 1;
    }
}
