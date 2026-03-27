<?php
// app/Http/Controllers/Api/AuthController.php

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
    /**
     * Register a new user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'organization_name' => 'required|string|max:255',
        ]);

        // Create organization
        $organization = Organization::create([
            'name' => $request->organization_name,
            'slug' => Str::slug($request->organization_name) . '-' . uniqid(),
            'email' => $request->email,
            'status' => 'active',
        ]);

        // Create user
        $user = User::create([
            'organization_id' => $organization->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        // Assign admin role
        $user->assignRole('Admin');

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'organization' => $organization,
            'user' => $user,
        ], 'Registration successful', 201);
    }

    /**
     * Login user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::with('organization')
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

    // Optional: delete old tokens (recommended)
    $user->tokens()->delete();

    $token = $user->createToken('auth_token')->plainTextToken;

    return $this->sendResponse([
        'token_type' => 'Bearer',
        'token' => $token,
        'user' => $user,
    ], 'Login successful');
}

    /**
     * Logout user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse(null, 'Logged out successfully');
    }

    /**
     * Get authenticated user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['organization', 'roles', 'permissions', 'employee']);

        return $this->sendResponse($user, 'User retrieved successfully');
    }

    /**
     * Send password reset link
     * 
     * @param Request $request
     * @return JsonResponse
     */
    
    /**
     * Update user avatar
     */
    public function updateAvatar(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->image);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->image = $path;
            $user->save();

            return $this->sendResponse(new \App\Http\Resources\UserResource($user), 'Avatar updated successfully');
        }

        return $this->sendError('Failed to upload avatar', [], 400);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->sendResponse(null, 'Password reset link sent to your email');
        }

        return $this->sendError('Unable to send reset link', ['email' => __($status)], 400);
    }

    /**
     * Reset password
     * 
     * @param Request $request
     * @return JsonResponse
     */
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

