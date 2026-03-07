<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseController
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with(['branch', 'roles', 'permissions'])
            ->where('email', $request->email)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            return $this->sendError('Your account is not active.', [], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->sendResponse([
            'token_type' => 'Bearer',
            'token' => $token,
            'user' => $user
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse(null, 'Logged out successfully');
    }

    public function me(Request $request)
    {
        $user = $request->user()->load([
            'branch',
            'roles',
            'permissions'
        ]);

        return $this->sendResponse($user, 'User retrieved successfully');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->sendResponse(null, 'Password reset link sent');
        }

        return $this->sendError('Unable to send reset link', ['email' => __($status)], 400);
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->password_changed_at = now();
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->sendResponse(null, 'Password reset successfully');
        }

        return $this->sendError('Unable to reset password', ['email' => __($status)], 400);
    }
}