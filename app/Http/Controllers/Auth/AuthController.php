<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string|max:20|exists:users,user_name',
            'password' => 'required|string|max:20',
        ]);

        $credentials = $request->only('user_name', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        $user = User::where('user_name', $request->user_name)->first();

        return response()->json([
            'accessToken' => $token,
            'userData' => [
                'fullName' => $user->first_name . ' ' . $user->last_name,
                'id' => $user->id,
                'role' => $user->getRoleNames(),
                'username' => $user->user_name
            ],
            'userAbilityRules' => [['action' => "manage", 'subject' => "all"]],
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL(),
        ]);
    }
}
