<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\CustomException;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LoginSupplierRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Jobs\SendOtpSmsJob;
use App\Models\Commerce\Supplier;
use App\Models\User;
use App\Services\Base\BaseService;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController
{
    protected $baseService;

    public function __construct()
    {
        $this->baseService = new BaseService;
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('username', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'errors' => [
                        'password' => [
                            "رمز عبور برای نام کاربری {$credentials['username']} معتبر نمی‌باشد.",
                        ],
                    ],
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token'], 500);
        }

        $user = User::where('username', $request->username)->first();

        if ($user->hasRole('Super Admin')) {
            $permissions = [['action' => 'manage', 'subject' => 'all']];
        } else {
            $allUserPermissions = $user->getAllPermissions();

            if (count($allUserPermissions) === 0) {
                $permissions = [['action' => 'fuck', 'subject' => 'every-body']];
            } else {
                $permissions = $allUserPermissions->pluck('name')->map(function ($permission) {
                    return ['action' => explode(' ', $permission)[0], 'subject' => explode(' ', $permission)[1]];
                });
            }
        }

        return response()->json([
            'accessToken' => $token,
            'userData' => [
                'fullName' => $user->first_name . ' ' . $user->last_name,
                'id' => $user->id,
                'role' => $user->getRoleNames(),
                'username' => $user->username,
            ],
            'userAbilityRules' => $permissions,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL(),
        ], 200);
    }

    public function loginSupplier(LoginSupplierRequest $request)
    {
        $supplier = $this->baseService->getByFiltersWithRelations(
            Supplier::class,
            [['tel1', '=', $request->mobileNumber]],
            []
        )->first();

        if (time() < $supplier->otp_expires_at) {
            throw new CustomException('کد تأیید قبلاً برای شما ارسال شده‌است. برای ارسال دوباره لطفاً منتظر بمانید.');
        }

        $otpCode = random_int(100000, 999999);
        $otpExpiresAt = time() + 120;

        $supplier->otp_code = $otpCode;
        $supplier->otp_expires_at = $otpExpiresAt;

        $supplier->save();

        SendOtpSmsJob::dispatch($otpCode, $supplier->tel1);

        return ['otpExpiresAt' => $otpExpiresAt];
    }

    public function verifySupplierOtp(VerifyOtpRequest $request)
    {
        $supplier = $this->baseService->getByFiltersWithRelations(
            Supplier::class,
            [['tel1', '=', $request->mobileNumber]],
            []
        )->first();

        if ($supplier->otp_code != $request->otpCode) {
            return throw new CustomException('کد تأیید وارد شده اشتباه است.', 400);
        }

        if (time() > $supplier->otp_expires_at) {
            throw new CustomException('کد تأیید منقضی شده است. لطفاً دوباره درخواست ارسال کنید.', 400);
        }

        try {
            $token = Auth::guard('supplier')->login($supplier);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token'], 500);
        }

        $userAbilityRules = Role::where('name', 'supplier')->first()->permissions->pluck('name')->map(function ($permission) {
            return ['action' => explode(' ', $permission)[0], 'subject' => explode(' ', $permission)[1]];
        });

        return response()->json([
            'accessToken' => $token,
            'userData' => [
                'fullName' => $supplier->name,
                'id' => $supplier->id,
                'role' => ['supplier'],
                'username' => $supplier->supplier_id,
            ],
            'userAbilityRules' => $userAbilityRules,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL(),
        ], 200);
    }
}
