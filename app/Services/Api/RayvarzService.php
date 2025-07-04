<?php

namespace App\Services\Api;

use App\Exceptions\CustomException;
use App\Jobs\SyncWithRayvarz;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RayvarzService
{
    public function sync($request)
    {
        $modelName = $request->query('model_name');
        $uniqueBy = $request->query('unique_by');

        SyncWithRayvarz::dispatch($modelName, $uniqueBy);
    }

    public function fetchByFilters($modelName, $filters)
    {
        $records = [];

        $index = 0;

        Log::info('Fetching records from Rayvarz', [
            'modelName' => $modelName,
            'filters' => $filters,
        ]);

        try {
            while (true) {
                $response = Http::withHeaders([
                    'access_token' => $this->getAccessToken(),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->post(
                        env('RAYVARZ_FETCH') . $modelName . '/List',
                        [
                            'Index' => $index,
                            ...$filters,
                        ],
                    );

                if (!$response->successful()) {
                    Log::error('Error fetching records from Rayvarz', [
                        'modelName' => $modelName,
                        'filters' => $filters,
                        'response' => $response,
                    ]);
                    throw new CustomException('هنگام دریافت رکورد‌های جدول پایه ' . $modelName . ' از رایورز خطایی رخ داده‌است.');
                }

                $recordsPerSheet = $response->json();

                if (count($recordsPerSheet) === 0) {
                    break;
                }

                $records = array_merge($records, $recordsPerSheet);

                $index++;
            }

            return $records;
        } catch (\Exception $e) {
            Log::error('Error fetching records from Rayvarz', [
                'modelName' => $modelName,
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);
            throw new CustomException('هنگام دریافت رکورد‌های جدول پایه ' . $modelName . ' از رایورز خطایی رخ داده‌است.');
        }
    }

    public function syncByFilters($modelName, $uniqueBy, $filters)
    {
        $records = $this->fetchByFilters($modelName, $filters);

        $tableName = $modelName . 's';

        $chunkSize = 200;
        $columns = Schema::getColumnListing($tableName);

        foreach (array_chunk($records, $chunkSize) as $chunk) {
            $filtered = array_map(function (array $record) use ($columns) {
                return array_intersect_key($record, array_flip($columns));
            }, $chunk);
            DB::table($tableName)->upsert($filtered, [$uniqueBy]);
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
                    '4430',
                    'bfb0f696b1e315716e67e56e4862bfdaba6ed0d391d16985b0d00dbd49abaa87',
                ],
            )->json();
    }
}
