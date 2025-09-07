<?php

namespace App\Services\Api;

use App\Jobs\SyncWithKasraJob;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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

        $userData = [];
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
        }

        foreach (array_chunk($userData, 200) as $chunk) {
            DB::table('users')->upsert($chunk, ['personnel_code']);
        }

        Log::info('Sync completed');

    }
}
