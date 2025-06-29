<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Jobs\SendOtpSmsJob;
use App\Models\Commerce\Supplier;
use App\Services\Base\BaseService;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Exceptions\CustomException;
use App\Services\Api\RayvarzService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\LoginSupplierRequest;

class AuthController
{
    protected $baseService;
    protected $rayvarzService;

    public function __construct()
    {
        $this->baseService = new BaseService;
        $this->rayvarzService = new RayvarzService;
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

        $user = User::whereUsername($request->username)->first();

        if ($user->hasRole('Super Admin')) {
            $permissions = [['action' => 'manage', 'subject' => 'all']];
        } else {
            $allUserPermissions = $user->getAllPermissions();

            if (count($allUserPermissions) === 0) {
                $permissions = [['action' => 'fuck', 'subject' => 'every-body']];
            } else {
                $permissions = $this->getUserAbilityRules($allUserPermissions);
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
        $supplier = Supplier::whereTel1($request->mobileNumber)->first();

        if (!$supplier) {
            $supplierInRayvarz = $this->findSupplierInRayvarz($request->mobileNumber);
            // TODO: maybe you can make this reusable later
            if (!$supplierInRayvarz) {
                throw new CustomException('شماره تلفن همراه شما در سیستم ثبت نشده است.', 404);
            }
            $supplier = Supplier::updateOrCreate(
                ['supplierId' => $supplierInRayvarz['supplierId']],
                $supplierInRayvarz
            );
        }

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
        $supplier = Supplier::whereTel1($request->mobileNumber)->first();

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

        return response()->json([
            'accessToken' => $token,
            'userData' => [
                'fullName' => $supplier->name,
                'id' => $supplier->id,
                'role' => ['supplier'],
                'username' => $supplier->supplier_id,
            ],
            'userAbilityRules' => $this->getUserAbilityRules(Role::whereName('supplier')->first()->permissions),
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL(),
        ], 200);
    }

    private function getUserAbilityRules($permissions)
    {
        // get userAbilityRules by this format:
        /*
        [
            {
                action: 'read',
                subject: 'something'
            },
            {
                action: 'write',
                subject: 'something else'
            }
        ]
        */
        return $permissions->pluck('name')->map(function ($permission) {
            return ['action' => explode(' ', $permission)[0], 'subject' => explode(' ', $permission)[1]];
        });
    }

    private function findSupplierInRayvarz($mobileNumber)
    {
        return $this->rayvarzService->fetchByFilters('supplier', ['WhereClause' => "tel1.equals(\"{$mobileNumber}\")"]);
    }
}
