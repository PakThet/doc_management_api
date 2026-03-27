<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Requests\Api\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class UserController extends BaseController
{
    /**
     * List Users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // If filtering by a role, check if it's a global role
        if ($request->role) {
            $roleToFilter = Role::find($request->role);
            
            // If the role exists and is global, temporarily remove the organization scope
            if ($roleToFilter && is_null($roleToFilter->organization_id)) {
                $query->withoutGlobalScope(\App\Traits\HasOrganizationScope::class);
            }
        }

        $users = $query->with(['organization', 'employee', 'roles', 'permissions'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->role, function ($query, $roleId) {
                $role = \App\Models\Role::find($roleId);
                if ($role) {
                    $query->role($role);
                } else {
                    $query->role($roleId);
                }
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_direction ?? 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->sendPaginated(
            UserResource::collection($users),
            'Users retrieved successfully'
        );
    }

    /**
     * Create User
     */
    public function store(UserRequest $request)
    {
        $user = User::create([
            'organization_id' => $this->getOrganizationId(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'bio' => $request->bio,
            'image' => $request->image,
            'password' => Hash::make($request->password),
            'status' => $request->status ?? 'active',
            'two_factor_enabled' => $request->two_factor_enabled ?? false,
        ]);

        if ($request->filled('roles')) {
            $user->syncRoles($request->roles);
        }

        return $this->sendResponse(
            new UserResource($user->load('organization')),
            'User created successfully',
            201
        );
    }

    /**
     * Show User
     */
    public function show(User $user)
    {
        return $this->sendResponse(
            new UserResource($user->load(['organization', 'employee', 'roles', 'permissions'])),
            'User retrieved successfully'
        );
    }

    /**
     * Update User
     */
    public function update(UserRequest $request, User $user)
    {
        $user->update($request->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'bio',
            'image',
            'status',
            'two_factor_enabled'
        ]));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->password_changed_at = Carbon::now();
            $user->save();
        }

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return $this->sendResponse(
            new UserResource($user->load('organization')),
            'User updated successfully'
        );
    }

    /**
     * Delete User
     */
    public function destroy(User $user)
    {
        if ($user->employee) {
            return $this->sendError(
                'Cannot delete user with associated employee record.',
                [],
                422
            );
        }

        $user->delete();

        return $this->sendResponse(null, 'User deleted successfully');
    }

    /**
     * Assign Roles
     */
    public function assignRoles(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name'
        ]);

        $user->syncRoles($request->roles);

        return $this->sendResponse(
            $user->getRoleNames(),
            'Roles assigned successfully'
        );
    }
}

