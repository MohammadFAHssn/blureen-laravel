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

        $usersMap = User::pluck('id', 'personnel_code');

        $userData = [];
        $userProfile = [];
        foreach ($users as $user) {

            if (strlen($user['Code']) !== 4) {
                continue;
            }

            $userData[] = [
                'first_name' => $user['FName'],
                'last_name' => $user['LName'],
                'username' => $user['Code'],
                'personnel_code' => $user['Code'],
                'active' => false,
                'updated_at' => now(),
            ];

            $userProfile[] = [
                'user_id' => $usersMap[$user['Code']],
                'mobile_number' => $user['MobileNO'] ? ('0' . Str::substr((string) $user['MobileNO'], -10)) : null,
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($userData, 500) as $chunk) {
            DB::table('users')->upsert($chunk, ['username']);
        }

        foreach (array_chunk($userProfile, 500) as $chunk) {
            UserProfile::upsert($chunk, ['user_id']);
        }

        Log::info('Sync completed');

    }
}
