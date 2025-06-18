<?php

namespace App\Services\Api;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\get;

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
        while (true) {
            $recordsPerSheet = Http::withHeaders([
                'access_token' => $this->getAccessToken(),
                'Content-Type' => 'application/json',
            ])->post(
                    env('RAYVARZ_FETCH') . $baseTableName . '/List',
                    [
                        "Index" => $index,
                        // "WhereClause" => "tel1.equals(\"9137234176\")",
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
    }

    private function getAccessToken()
    {
        try {
            $token = cache()->get('rayvarz_access_token');
            if ($token) {
                return $token;
            }
        } catch (\Exception $e) {
            Log::error('Error fetching Rayvarz access token from cache: ' . $e->getMessage());
        }
        $token = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post(
                env('RAYVARZ_GET_ACCESS_TOKEN'),
                [
                    "4430",
                    "bfb0f696b1e315716e67e56e4862bfdaba6ed0d391d16985b0d00dbd49abaa87"
                ],
            )->json();

        return $token;

    }
}
