<?php

namespace App\Services\Api;

use App\Models\User;
use App\Jobs\SyncWithKasraJob;
use App\Models\Base\UserProfile;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KasraService
{
    public function sync()
    {
        SyncWithKasraJob::dispatch();
    }

    public function fetchUsers()
    {
        Log::info('Fetching users from Kasra');

        try {
            $response = Http::timeout(60)->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post(
                    config('services.kasra.fetch.users'),
                );

            if ($response->failed()) {
                Log::error('Error fetching users from Kasra', [
                    'response' => $response,
                ]);
                throw new CustomException('هنگام دریافت اطلاعات کاربران از کسرا خطایی رخ داده‌است.', 500);
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error fetching users from Kasra', [
                'error' => $e->getMessage(),
            ]);
            throw new CustomException('هنگام دریافت اطلاعات کاربران از کسرا خطایی رخ داده‌است.', 500);
        }
    }

    public function syncUsers()
    {
        $users = $this->fetchUsers();

        $users = arabicToPersian($users['data']);

        Log::info('Syncing users to database', [
            'userCount' => count($users),
        ]);

        foreach ($users as $rayvarzUser) {

            if (strlen($rayvarzUser['Code']) !== 4) {
                continue;
            }

            $ourUser = User::updateOrCreate(
                [
                    'user_name' => $rayvarzUser['Code'],
                ],
                [
                    'first_name' => $rayvarzUser['FName'],
                    'last_name' => $rayvarzUser['LName'],
                    'username' => $rayvarzUser['Code'],
                    'active' => false,
                ]
            );

            UserProfile::updateOrCreate(
                [
                    'user_id' => $ourUser->id,
                ],
                [
                    'mobile_number' => $rayvarzUser['MobileNO'] ? ('0' . Str::substr((string) $rayvarzUser['MobileNO'], -10)) : null,
                ]
            );
        }

        Log::info('Sync completed');

    }
}
