<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Models\User;

// ─────────────────────────────────────────────────────────────────────────────
// RoleController
// ─────────────────────────────────────────────────────────────────────────────

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super-admin');
    }

    public function index(): JsonResponse
    {
        $roles = QueryBuilder::for(Role::class)
            ->allowedFilters([AllowedFilter::partial('name')])
            ->allowedIncludes(['permissions'])
            ->allowedSorts(['name', 'created_at'])
            ->paginate(request()->integer('per_page', 15));

        return response()->json($roles);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|unique:roles,name',
            'guard_name'    => 'nullable|string',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);
        if ($validated['name'] === 'super-admin') {
            return response()->json([
                'message' => 'Super Admin role already exists and cannot be recreated.'
            ], 403);
        }

        $role = Role::create([
            'name'       => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'api',
        ]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json($role->load('permissions'), 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json($role->load('permissions'));
    }

    // can disable 
    public function update(Request $request, Role $role): JsonResponse
    {
        if ($role->name === 'super-admin') {
            return response()->json([
                'message' => 'Super Admin role cannot be modified.'
            ], 403);
        }

        $validated = $request->validate([
            'name'          => "sometimes|string|unique:roles,name,{$role->id}",
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->update(['name' => $validated['name'] ?? $role->name]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json($role->load('permissions'));
    }

    public function destroy(Role $role): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user->hasRole('super-admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($role->name === 'super-admin') {
            return response()->json([
                'message' => 'Super Admin role cannot be deleted.'
            ], 403);
        }

        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete role assigned to users.'
            ], 400);
        }
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    }


    public function addPermission(Request $request, Role $role)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user->hasRole('super-admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($role->name === 'super-admin') {
            return response()->json(['message' => 'Cannot modify the super-admin Permission'], 403);
        }
        $validated = $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'message' => 'Permissions synced successfully',
            'role' => $role->name,
            'permissions' => $role->permissions
        ]);
    }

    public function permissions(Role $role): JsonResponse
    {
        return response()->json([
            'role' => $role->name,
            'permissions' => $role->permissions->pluck('name'),
        ]);
    }

    public function matrix(): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        $matrix = [];

        foreach ($roles as $role) {

            $rolePermissions = $role->permissions->pluck('name')->toArray();

            $permissionMap = [];

            foreach ($permissions as $permission) {
                $permissionMap[$permission->name] = in_array(
                    $permission->name,
                    $rolePermissions
                );
            }

            $matrix[] = [
                'role' => $role->name,
                'permissions' => $permissionMap,
            ];
        }

        return response()->json([
            'roles' => $roles->pluck('name'),
            'permissions' => $permissions->pluck('name'),
            'matrix' => $matrix,
        ]);
    }
}
