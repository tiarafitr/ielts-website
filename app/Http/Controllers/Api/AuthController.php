<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

/**
 * Sanctum personal access tokens. The SPA stores the returned token and sends
 * it as "Authorization: Bearer <token>".
 */
class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => strtolower($request->validated('email')),
            'password' => Hash::make($request->validated('password')),
        ]);

        return $this->api([
            'message' => 'Account created successfully.',
            'data' => [
                'token' => $user->createToken('ielts-speaking')->plainTextToken,
                'user' => $user,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', strtolower($request->validated('email')))
            ->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return $this->api([
                'message' => 'These credentials do not match our records.',
            ], 401);
        }

        $user->tokens()->delete();

        return $this->api([
            'message' => 'Signed in successfully.',
            'data' => [
                'token' => $user->createToken('ielts-speaking')->plainTextToken,
                'user' => $user,
            ],
        ]);
    }

    public function me(): JsonResponse
    {
        return $this->api(['data' => auth('sanctum')->user()]);
    }

    public function logout(): JsonResponse
    {
        auth('sanctum')->user()->currentAccessToken()->delete();

        return $this->api(['message' => 'Signed out successfully.']);
    }
}
