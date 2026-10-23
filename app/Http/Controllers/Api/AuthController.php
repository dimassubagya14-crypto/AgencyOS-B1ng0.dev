<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // POST /api/login
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $user = $request->user();

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Akun ini nonaktif. Hubungi administrator.',
            ]);
        }

        $token = $user->createToken('admin-panel')->plainTextToken;

        ActivityLog::record('login', 'users', $user->id, "{$user->name} login");

        return response()->json([
            'token' => $token,
            'user' => $user->only('id', 'name', 'email', 'role'),
        ]);
    }

    // POST /api/logout
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        ActivityLog::record('logout', 'users', $user->id, "{$user->name} logout");

        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    // GET /api/me
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
