<?php
namespace App\Services\Base;

use App\Exceptions\CustomException;
use App\Repositories\Base\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserService
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function getApprovalFlowsAsRequester($request)
    {
        $requestTypeId = $request['requestTypeId'];

        return $this->userRepository->getApprovalFlowsAsRequester($requestTypeId);
    }

    public function resetPassword($request)
    {
        $user = auth()->user();

        $hashedPassword = Hash::make($request['newPassword']);

        $user->password = $hashedPassword;
        $user->save();

        $this->alsoResetPasswordInLegacyIntegratedSystem($user, $hashedPassword);
    }

    private function alsoResetPasswordInLegacyIntegratedSystem($user, $newPassword)
    {
        try {
            $response = Http::withoutVerifying()
                ->post(
                    config('services.legacy_integrated_system.reset_password'),
                    [
                        'token' => config('services.legacy_integrated_system.token'),
                        'personnel_code' => $user->personnel_code,
                        'new_password' => $newPassword,
                    ],
                );

            if ($response->failed()) {
                Log::error('Failed to reset password in legacy integrated system', [
                    'status' => $response->status(),
                    'body' => Str::limit($response->body(), 2000),
                ]);
                throw new CustomException('هنگام تغییر پسورد در سیستم جامع قدیمی خطایی رخ داده‌است.', 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to reset password in legacy integrated system', [
                'error' => $e->getMessage(),
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 2000),
            ]);
            throw new CustomException('هنگام تغییر پسورد در سیستم جامع قدیمی خطایی رخ داده‌است.', 500);
        }
    }
}
