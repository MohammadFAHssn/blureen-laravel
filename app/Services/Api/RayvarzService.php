<?php

namespace App\Services\Api;

use App\Models\User;
use Illuminate\Support\Str;
use App\Jobs\SyncWithRayvarzJob;
use App\Models\Base\RetiredUsers;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use App\Enums\Rayvarz as RayvarzEnums;
use App\Models\Base\UserProfile;

class RayvarzService
{
    public function sync($request)
    {
        $params = $request->route()->parameters();

        $module = Str::studly($params['module']);
        $modelName = Str::studly($params['model_name']);

        $uniqueBy = $request->query('unique_by');

        SyncWithRayvarzJob::dispatch($module, $modelName, $uniqueBy);
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

    public function syncByFilters($module, $modelName, $uniqueBy, $filters)
    {
        $records = $this->fetchByFilters($modelName, $filters);

        $records = arabicToPersian($records);

        $tableName = Str::snake($modelName) . 's';

        $chunkSize = 200;
        $columns = Schema::getColumnListing($tableName);

        Log::info('Syncing records to database', [
            'modelName' => $modelName,
            'recordCount' => count($records),
        ]);

        $modelClass = '\\App\\Models\\' . $module . '\\' . $modelName;

        $lastUpdatedAt = $modelClass::query()->latest('updated_at')->value('updated_at');

        foreach (array_chunk($records, $chunkSize) as $chunk) {
            $filtered = array_map(function (array $record) use ($columns) {
                $filteredRecord = array_intersect_key($record, array_flip($columns));
                $filteredRecord['updated_at'] = now();
                return $filteredRecord;
            }, $chunk);
            DB::table($tableName)->upsert($filtered, [$uniqueBy]);
        }

        $modelClass::query()->where('updated_at', '<=', $lastUpdatedAt)
            ->orWhereNull('updated_at')
            ->delete();

        Log::info('Sync completed');
    }

    public function syncUsers()
    {
        $users = $this->fetchUsers();

        $users = arabicToPersian($users);

        $rayvarzCities = $this->fetchReports(4434);

        info('Syncing users to database', [
            'userCount' => count($users),
        ]);

        foreach ($users as $rayvarzYser) {
            $ourUser = User::updateOrCreate(
                ['username' => $rayvarzYser['personnelId']],
                [
                    'first_name' => $rayvarzYser['name'],
                    'last_name' => $rayvarzYser['family'],
                    'username' => $rayvarzYser['personnelId'],
                    'personnel_code' => $rayvarzYser['personnelId'],
                    'active' => $rayvarzYser["quitDate"] ? false : true,
                ]
            );

            UserProfile::updateOrCreate(
                ['user_id' => $ourUser->id],
                [
                    'national_code' => $rayvarzYser['nationalCode'],
                    'gender' => $rayvarzYser['genderId'] === 1 ? 'مرد' : 'زن',
                    'father_name' => $rayvarzYser['fatherName'],
                    'birth_place' => $rayvarzCities->firstWhere('rayvarz_id', $rayvarzYser['birthPlaceId'])['name'] ?? null,
                    'birth_date' => jalalianYmdDateToCarbon($rayvarzYser['birthDate']),
                    'marital_status' => $this->getMaritalStatusById($rayvarzYser['mariageStatusId']),
                    'employment_date' => jalalianYmdDateToCarbon($rayvarzYser['employmentDate']),
                    'start_date' => jalalianYmdDateToCarbon($rayvarzYser['assignmentStartDate']),
                    'education_level_id' => $rayvarzYser['currentEducationId'],
                    'workplace_id' => $rayvarzYser['currentLocation'],
                    'work_area_id' => $rayvarzYser['crnZoneID'],
                    'cost_center_id' => $rayvarzYser['currentCenterId'],
                    'job_position_id' => $rayvarzYser['currentPostId'],
                ]
            );
        }

        $retiredUsers = RetiredUsers::all();
        foreach ($retiredUsers as $retiredUser) {
            User::where('personnel_code', $retiredUser->personnel_code)->update([
                'active' => true,
            ]);
        }

        info('Sync completed');
    }

    public function fetchReports($reportNumber)
    {
        $reports = RayvarzEnums::REPORTS;

        info('Fetching report from Rayvarz', [
            'reportNumber' => $reportNumber,
            'reportName' => $reports[$reportNumber] ?? 'undefined',
        ]);

        try {
            $response = Http::timeout(60)->withHeaders([
                'AccessToken' => $this->getAccessTokenForReports()
            ])
                ->get(
                    config('services.rayvarz.fetch.reports'),
                    [
                        'systemId' => 'rayemp',
                        'groupId' => '17',
                        'reportNo' => (string) $reportNumber,
                        'Level' => '1',
                        'parametersString' => '',
                        'filterData' => '',
                        'Language' => '0',
                    ],
                );

            if ($response->failed()) {
                Log::error('Rayvarz fetch failed', [
                    'report' => $reports[$reportNumber] ?? $reportNumber,
                    'status' => $response->status(),
                    'url' => optional($response->transferStats)->getEffectiveUri()?->__toString(),
                    'body' => Str::limit($response->body(), 2000),
                ]);
                throw new CustomException('هنگام دریافت ' . ($reports[$reportNumber] ?? '"گزارشی که تعریف نشده‌است"') . ' از رایورز خطایی رخ داده‌است.', 500);
            }

            $decoded = html_entity_decode($response->body(), ENT_QUOTES, 'UTF-8');
            $xml = simplexml_load_string($decoded, 'SimpleXMLElement', LIBXML_NOCDATA);
            $xml->registerXPathNamespace('t', 'http://tempuri.org/');
            $rows = $xml->xpath('/t:string/t:DocumentElement/t:GetReportFilterDataAsXml');
            $result = collect($rows)->map(function ($item) {
                return [
                    'rayvarz_id' => (int) $item->a1,
                    'name' => trim((string) $item->a2),
                ];
            })->values();


            return collect($result);

        } catch (\Exception $e) {
            Log::error('Rayvarz fetch failed', [
                'report' => $reports[$reportNumber] ?? $reportNumber,
                'status' => $response->status(),
                'url' => optional($response->transferStats)->getEffectiveUri()?->__toString(),
                'body' => Str::limit($response->body(), 2000),
            ]);
            throw new CustomException('هنگام دریافت ' . ($reports[$reportNumber] ?? '"گزارشی که تعریف نشده‌است"') . ' از رایورز خطایی رخ داده‌است.', 500);
        }
    }

    public function syncReports($reportNumber)
    {
        $reports = RayvarzEnums::REPORTS;

        $report = arabicToPersian($this->fetchReports($reportNumber)->toArray());

        $tableName = $reports[$reportNumber];

        info('Syncing report to database', [
            'reportNumber' => $reportNumber,
            'reportName' => $tableName,
            'recordCount' => count($report),
        ]);

        $chunkSize = 200;
        $columns = Schema::getColumnListing($tableName);

        foreach (array_chunk($report, $chunkSize) as $chunk) {
            $filtered = array_map(function (array $record) use ($columns) {
                $filteredRecord = array_intersect_key($record, array_flip($columns));
                $filteredRecord['updated_at'] = now();
                return $filteredRecord;
            }, $chunk);
            DB::table($tableName)->upsert($filtered, ['rayvarz_id']);
        }

        info('Sync completed for report', [
            'reportNumber' => $reportNumber,
            'reportName' => $tableName,
        ]);

    }

    private function getAccessToken()
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',

        ])->post(
                config('services.rayvarz.get_access_token'),
                [
                    config('services.rayvarz.username'),
                    'bfb0f696b1e315716e67e56e4862bfdaba6ed0d391d16985b0d00dbd49abaa87',
                ],
            )->json();
    }

    private function getAccessTokenForReports()
    {
        return Http::asForm()->post(
            config('services.rayvarz.get_access_token_for_reports'),
            [
                'grant_type' => 'password',
                'username' => config('services.rayvarz.username'),
                'password' => config('services.rayvarz.password'),
                'client_id' => config('services.rayvarz.client_id'),
                'client_secret' => config('services.rayvarz.client_secret'),
            ],
        )->json()['access_token'];
    }

    private function getMaritalStatusById($id)
    {
        $statuses = [
            1 => 'مجرد',
            2 => 'متاهل',
            3 => 'معیل',
        ];

        return $statuses[$id] ?? null;
    }
}
