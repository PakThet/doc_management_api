<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseController
{
    // ─── Login ────────────────────────────────────────────────────────────────

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            return $this->sendError(
                'Your account is ' . $user->status . '. Please contact support.',
                [],
                403,
            );
        }
        
        // $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        activity()
            ->causedBy($user)
            ->withProperties(['ip' => $request->ip(), 'user_agent' => $request->userAgent()])
            ->log('User logged in');

        return $this->sendResponse([
            'access_token' => $token,
            'user'         => $user,
        ], 'Login successful.');
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    public function logout(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        activity()
            ->causedBy($user)
            ->withProperties(['ip' => $request->ip()])
            ->log('User logged out');

        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse(null, 'Logged out successfully.');
    }

    // ─── Authenticated user ───────────────────────────────────────────────────

    public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user()->load(['branch', 'roles']);

        return $this->sendResponse(['user' => $user], 'Me fetching successfully');
    }

    // ─── Update profile ───────────────────────────────────────────────────────

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name'  => 'sometimes|string|max:255',
            'phone'      => "nullable|string|unique:users,phone,{$user->id}",
            'bio'        => 'nullable|string',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        $user->update($validated);
        $user->refresh();

        activity()->causedBy($user)->log('User updated profile');
        
        return $this->sendResponse(['user' => $user], 'Profile updated successfully.');
    }

    // ─── Change password ──────────────────────────────────────────────────────

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password'            => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        $user->tokens()->delete();

        activity()->causedBy($user)->log('User changed password');

        return $this->sendResponse(null, 'Password changed successfully. Please log in again.');
    }

    // ─── Forgot password ──────────────────────────────────────────────────────

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->sendError(__($status), [], 400);
        }

        return $this->sendResponse(null, 'Password reset link sent to your email.');
    }

    // ─── Reset password ───────────────────────────────────────────────────────

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'            => Hash::make($password),
                    'remember_token'      => Str::random(60),
                    'password_changed_at' => now(),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));

                activity()->causedBy($user)->log('User reset password via email');
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->sendError(__($status), [], 400);
        }

        return $this->sendResponse(null, 'Password has been reset successfully. Please log in.');
    }

    // ─── Email verification ───────────────────────────────────────────────────

    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return $this->sendError('Invalid verification link.', [], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->sendResponse(null, 'Email already verified.');
        }

        $user->markEmailAsVerified();

        activity()->causedBy($user)->log('User verified email');

        return $this->sendResponse(null, 'Email verified successfully.');
    }

    public function resendVerification(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->sendError('Email is already verified.', [], 400);
        }

        $user->sendEmailVerificationNotification();

        return $this->sendResponse(null, 'Verification email resent.');
    }

    // ─── Two-Factor toggle ────────────────────────────────────────────────────

    public function toggleTwoFactor(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->update(['two_factor_enabled' => ! $user->two_factor_enabled]);

        $state = $user->two_factor_enabled ? 'enabled' : 'disabled';

        activity()->causedBy($user)->log("User {$state} two-factor authentication");

        return $this->sendResponse(
            ['two_factor_enabled' => $user->two_factor_enabled],
            "Two-factor authentication {$state}.",
        );
    }
}