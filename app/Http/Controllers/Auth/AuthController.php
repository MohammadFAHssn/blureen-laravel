<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. اعتبارسنجی ورودی
        $request->validate([
            'user_name' => 'required|string|exists:users,user_name',
            'password' => 'required|string',
        ]);

        // 2. ساخت آرایه Credentials
        $credentials = $request->only('user_name', 'password');

        try {
            // 3. تلاش برای صدور توکن
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        // 4. پاسخ با توکن
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }

}
