<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Логин по имени (без пароля). Возвращает API-токен.
     */
    public function login(Request $request): JsonResponse
    {
        $name = $request->input('name');

        if (empty($name) || ! is_string($name)) {
            return response()->json(['message' => 'Name is required'], 422);
        }

        $user = User::query()->where('name', $name)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->tokens()->where('name', 'login')->delete();
        $token = $user->createToken('login')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Текущий пользователь (для проверки авторизации).
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
        ]);
    }

    /**
     * Выход (инвалидация токена).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
