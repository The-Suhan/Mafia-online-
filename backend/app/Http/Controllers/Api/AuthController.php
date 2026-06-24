<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ─────────────────────────────────────────────
    //  POST /api/register
    // ─────────────────────────────────────────────

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'nickname' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'unique:users',
                'regex:/^[a-zA-Z0-9_]+$/',
            ],
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'nickname' => $request->nickname,
            'email' => $request->email,
            'password' => $request->password, // cast: hashed
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  POST /api/login
    // ─────────────────────────────────────────────

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        // Token expiry: 30 days if remember, 2 hours otherwise
        $expiration = $request->boolean('remember')
            ? now()->addDays(30)
            : now()->addHours(2);

        $token = $user->createToken('auth_token', ['*'], $expiration)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /api/logout
    // ─────────────────────────────────────────────

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /api/forgot-password
    // ─────────────────────────────────────────────

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Always return 200 regardless of whether the email exists (security)
        Password::sendResetLink(
            $request->only('email')
        );

        return response()->json([
            'message' => 'Reset link sent to your email',
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /api/reset-password
    // ─────────────────────────────────────────────

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password, // cast: hashed
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired reset link'],
            ]);
        }

        return response()->json([
            'message' => 'Password reset successfully',
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/user
    // ─────────────────────────────────────────────

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
