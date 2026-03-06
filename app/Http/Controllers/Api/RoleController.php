<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RoleController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | List Roles
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): JsonResponse
    {
        $roles = Role::where('organization_id', Auth::user()->organization_id)
            ->with('permissions')
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return $this->sendPaginated($roles, 'Roles retrieved successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | Create Role
    |--------------------------------------------------------------------------
    */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'api',
            'organization_id' => Auth::user()->organization_id,
        ]);

        if ($request->filled('permissions')) {

            $permissions = Permission::whereIn('name', $request->permissions)
                ->where('organization_id', Auth::user()->organization_id)
                ->get();

            $role->syncPermissions($permissions);
        }

        return $this->sendResponse($role->load('permissions'), 'Role created successfully', 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Role
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id): JsonResponse
    {
        $role = Role::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string',
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        if ($request->filled('permissions')) {

            $permissions = Permission::whereIn('name', $request->permissions)
                ->where('organization_id', Auth::user()->organization_id)
                ->get();

            $role->syncPermissions($permissions);
        }

        return $this->sendResponse($role->load('permissions'), 'Role updated successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Role
    |--------------------------------------------------------------------------
    */
    public function destroy($id): JsonResponse
    {
        $role = Role::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);

        $role->delete();

        return $this->sendResponse(null, 'Role deleted successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | Assign Permissions
    |--------------------------------------------------------------------------
    */
    public function assignPermissions(Request $request, $id): JsonResponse
    {
        $role = Role::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        $permissions = Permission::whereIn('name', $request->permissions)
            ->where('organization_id', Auth::user()->organization_id)
            ->get();

        $role->syncPermissions($permissions);

        return $this->sendResponse($role->load('permissions'), 'Permissions assigned successfully');
    }
}