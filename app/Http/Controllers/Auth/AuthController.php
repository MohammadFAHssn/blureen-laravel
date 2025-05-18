<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:20|exists:users,username',
            'password' => 'required|string|max:20',
        ]);

        $credentials = $request->only('username', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'errors' => [
                        'password' => [
                            "رمز عبور برای نام کاربری {$credentials['username']} معتبر نمی‌باشد."
                        ]
                    ]
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
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
                    return ['action' => explode(" ", $permission)[0], 'subject' => explode(" ", $permission)[1]];
                });
            }
        }

        return response()->json([
            'accessToken' => $token,
            'userData' => [
                'fullName' => $user->first_name . ' ' . $user->last_name,
                'id' => $user->id,
                'role' => $user->getRoleNames(),
                'username' => $user->username
            ],
            'userAbilityRules' => $permissions,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL(),
        ], 200);
    }
}
