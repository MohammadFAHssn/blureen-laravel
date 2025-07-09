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
                        config('services.rayvarz.fetch.other_models') . $modelName . '/List',
                        [
                            'Index' => $index,
                            ...$filters,
                        ],
                    );

                if ($response->failed()) {
                    Log::error('Error fetching records from Rayvarz', [
                        'modelName' => $modelName,
                        'filters' => $filters,
                        'response' => $response,
                    ]);
                    throw new CustomException('هنگام دریافت رکورد‌های جدول پایه ' . $modelName . ' از رایورز خطایی رخ داده‌است.', 500);
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
            throw new CustomException('هنگام دریافت رکورد‌های جدول پایه ' . $modelName . ' از رایورز خطایی رخ داده‌است.', 500);
        }
    }

    public function fetchUsers()
    {
        Log::info('Fetching users from Rayvarz');

        try {
            $response = Http::timeout(60)->withHeaders([
                'access_token' => $this->getAccessToken(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post(
                    config('services.rayvarz.fetch.users'),
                );

            if ($response->failed()) {
                Log::error('Error fetching users from Rayvarz', [
                    'response' => $response,
                ]);
                throw new CustomException('هنگام دریافت اطلاعات کاربران از رایورز خطایی رخ داده‌است.', 500);
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error fetching users from Rayvarz', [
                'error' => $e->getMessage(),
            ]);
            throw new CustomException('هنگام دریافت اطلاعات کاربران از رایورز خطایی رخ داده‌است.', 500);
        }
    }

    public function syncByFilters($modelName, $uniqueBy, $filters)
    {
        $records = $this->fetchByFilters($modelName, $filters);

        $tableName = $modelName . 's';

        $chunkSize = 200;
        $columns = Schema::getColumnListing($tableName);

        Log::info('Syncing records to database', [
            'modelName' => $modelName,
            'recordCount' => count($records),
        ]);

        foreach (array_chunk($records, $chunkSize) as $chunk) {
            $filtered = array_map(function (array $record) use ($columns) {
                return array_intersect_key($record, array_flip($columns));
            }, $chunk);
            DB::table($tableName)->upsert($filtered, [$uniqueBy]);
        }
    }

    public function syncUsers()
    {
        $users = $this->fetchUsers();

        Log::info('Syncing users to database', [
            'userCount' => count($users),
        ]);

        foreach ($users as $user) {
            $userData = [
                'first_name' => $user['name'],
                'last_name' => $user['family'],
                'username' => $user['personnelId'],
                'personnel_code' => $user['personnelId'],
                'mobile_number' => $user['mobile'],
                'active' => $user["quitDate"] ? false : true,
                'profile_image' => $user['personnelId'] . 'jpg',
                'updated_at' => now(),
            ];

            DB::table('users')->updateOrInsert(
                ['personnel_code' => $user['personnelId']],
                $userData
            );
        }
    }

    private function getAccessToken()
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',

        ])->post(
                config('services.rayvarz.get_access_token'),
                [
                    '4430',
                    'bfb0f696b1e315716e67e56e4862bfdaba6ed0d391d16985b0d00dbd49abaa87',
                ],
            )->json();
    }
}
