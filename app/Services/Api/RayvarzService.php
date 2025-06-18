<?php

namespace App\Services\Api;

use Illuminate\Support\Str;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RayvarzService
{
    public function sync($request)
    {
        $modelName = Str::studly($request->query('model'));

        return $modelName;
    }

    public function fetchByFilters($baseTableName, $filters)
    {
        $records = [];

        $index = 0;
        try {
            while (true) {
                $recordsPerSheet = Http::withHeaders([
                    'access_token' => $this->getAccessToken(),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->post(
                        env('RAYVARZ_FETCH') . $baseTableName . '/List',
                        [
                            "Index" => $index,
                            ...$filters
                        ],
                    )->json();

                if (count($recordsPerSheet) === 0) {
                    break;
                }

                $records = array_merge($records, $recordsPerSheet);

                $index++;
            }
            return $records;
        } catch (\Exception $e) {
            Log::error('Error fetching records from Rayvarz', [
                'baseTableName' => $baseTableName,
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);
            throw new CustomException('هنگام دریافت رکورد‌های جدول پایه ' . $baseTableName . ' از رایورز خطایی رخ داده‌است.');
        }
    }

    private function getAccessToken()
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',

        ])->post(
                env('RAYVARZ_GET_ACCESS_TOKEN'),
                [
                    "4430",
                    "bfb0f696b1e315716e67e56e4862bfdaba6ed0d391d16985b0d00dbd49abaa87"
                ],
            )->json();
    }
}
