<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

class AuthController extends Controller
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
            return response()->json([
                'message' => 'Your account is ' . $user->status . '. Please contact support.',
            ], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        activity()
            ->causedBy($user)
            ->withProperties(['ip' => $request->ip(), 'user_agent' => $request->userAgent()])
            ->log('User logged in');

        return response()->json([
            'message'      => 'Login successful.',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $this->userPayload($user),
        ]);
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

        return response()->json(['message' => 'Logged out successfully.']);
    }

    // ─── Authenticated user ───────────────────────────────────────────────────

    public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user()->load(['branch', 'roles', 'permissions']);

        return response()->json($this->userPayload($user));
    }

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

        return response()->json(['message' => 'Password changed successfully. Please log in again.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)], 400);
        }

        return response()->json(['message' => 'Password reset link sent to your email.']);
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
            return response()->json(['message' => __($status)], 400);
        }

        return response()->json(['message' => 'Password has been reset successfully. Please log in.']);
    }

    // ─── Email verification ───────────────────────────────────────────────────

    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json(['message' => 'Invalid verification link.'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        $user->markEmailAsVerified();

        activity()->causedBy($user)->log('User verified email');

        return response()->json(['message' => 'Email verified successfully.']);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email resent.']);
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
            // Delete old image from disk before storing the new one
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        $user->update($validated);

        activity()->causedBy($user)->log('User updated profile');

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user'    => $this->userPayload($user->fresh(['branch', 'roles', 'permissions'])),
        ]);
    }

    // ─── Two-Factor toggle ────────────────────────────────────────────────────

    public function toggleTwoFactor(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->update(['two_factor_enabled' => ! $user->two_factor_enabled]);

        $state = $user->two_factor_enabled ? 'enabled' : 'disabled';

        activity()->causedBy($user)->log("User {$state} two-factor authentication");

        return response()->json([
            'message'            => "Two-factor authentication {$state}.",
            'two_factor_enabled' => $user->two_factor_enabled,
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id'                  => $user->id,
            'first_name'          => $user->first_name,
            'last_name'           => $user->last_name,
            'full_name'           => $user->full_name,
            'email'               => $user->email,
            'phone'               => $user->phone,
            'image'     => $user->image
                ? asset('storage/' . $user->image)
                : null,
            'bio'                 => $user->bio,
            'status'              => $user->status,
            'branch_id'           => $user->branch_id,
            'branch'              => $user->relationLoaded('branch') ? $user->branch : null,
            'two_factor_enabled'  => $user->two_factor_enabled,
            'email_verified_at'   => $user->email_verified_at,
            'password_changed_at' => $user->password_changed_at,
            'roles'               => $user->relationLoaded('roles') ? $user->getRoleNames() : [],
            'permissions'         => $user->relationLoaded('permissions') ? $user->getAllPermissions()->pluck('name') : [],
            'created_at'          => $user->created_at,
        ];
    }
}