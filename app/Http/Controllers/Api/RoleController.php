<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Http\JsonResponse;

class RoleController extends BaseController
{
    /**
     * Set Team Context
     */
    private function setTeamContext(): void
    {
        $user = Auth::user();

        if (!$user || !$user->organization_id) {
            abort(403, 'Organization context is missing.');
        }

        app()[PermissionRegistrar::class]
            ->setPermissionsTeamId($user->organization_id);
    }

    /**
     * List Roles
     */
    public function index(Request $request): JsonResponse
    {
        $this->setTeamContext();

        $roles = Role::with('permissions')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return $this->sendPaginated($roles, 'Roles retrieved successfully');
    }

    /**
     * Create Role
     */
    public function store(Request $request): JsonResponse
    {
        $this->setTeamContext();

        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'api',
        ]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $this->sendResponse(
            $role->load('permissions'),
            'Role created successfully',
            201
        );
    }

    /**
     * Show Role
     */
    public function show($id): JsonResponse
    {
        $this->setTeamContext();

        $role = Role::findOrFail($id);

        return $this->sendResponse(
            $role->load('permissions'),
            'Role retrieved successfully'
        );
    }

    /**
     * Update Role
     */
    public function update(Request $request, $id): JsonResponse
    {
        $this->setTeamContext();

        $role = Role::findOrFail($id);

        if ($role->name === 'super-admin') {
            return $this->sendError('Super-admin role cannot be modified', [], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $this->sendResponse(
            $role->load('permissions'),
            'Role updated successfully'
        );
    }

    /**
     * Delete Role
     */
    public function destroy($id): JsonResponse
    {
        $this->setTeamContext();

        $role = Role::findOrFail($id);

        if ($role->name === 'super-admin') {
            return $this->sendError('Super-admin role cannot be deleted', [], 403);
        }

        if ($role->users()->exists()) {
            return $this->sendError('Cannot delete role assigned to users', [], 422);
        }

        $role->delete();

        return $this->sendResponse(null, 'Role deleted successfully');
    }

    /**
     * Get Permissions
     */
    public function permissions(): JsonResponse
    {
        $this->setTeamContext();

        $permissions = Permission::orderBy('name')->get();

        return $this->sendResponse(
            $permissions,
            'Permissions retrieved successfully'
        );
    }
}