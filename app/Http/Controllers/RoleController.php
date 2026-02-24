<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // GET ALL ROLES
    public function index()
    {
        return response()->json(Role::with('permissions')->get());
    }

    // CREATE ROLE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $role = Role::create([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    // SHOW ROLE
    public function show(Role $role)
    {
        return response()->json($role->load('permissions'));
    }

    // UPDATE ROLE
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->update([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Role updated successfully',
            'data' => $role
        ]);
    }

    // DELETE ROLE
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }

    // ASSIGN PERMISSIONS TO ROLE
    public function assignPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role->syncPermissions($request->permissions);

        return response()->json([
            'message' => 'Permissions assigned successfully',
            'data' => $role->load('permissions')
        ]);
    }

    public function assignRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user = \App\Models\User::findOrFail($userId);

        $user->syncRoles([$request->role]);

        return response()->json([
            'message' => 'Role assigned successfully'
        ]);
    }
}
