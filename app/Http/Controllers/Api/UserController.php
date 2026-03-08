<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view users')->only(['index', 'show']);
        $this->middleware('permission:create users')->only(['store']);
        $this->middleware('permission:edit users')->only(['update', 'assignRole', 'syncPermissions']);
        $this->middleware('permission:delete users')->only(['destroy']);
    }

    public function index(): JsonResponse
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        $query = QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::partial('first_name'),
                AllowedFilter::partial('last_name'),
                AllowedFilter::partial('email'),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['first_name', 'last_name', 'email', 'created_at', 'status'])
            ->allowedIncludes(['branch', 'roles', 'permissions']);
            if (! $authUser->hasRole('super-admin')) {
        $query->where('branch_id', $authUser->branch_id);
    }
        $users = $query->paginate(request()->integer('per_page', 15))
            ->appends(request()->query());

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id'   => 'nullable|exists:branches,id',
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'phone'       => 'nullable|string|unique:users,phone',
            'bio'         => 'nullable|string',
            'status'      => 'in:active,inactive,suspended',
            'password'    => 'required|string|min:8|confirmed',
            'role'        => 'nullable|string|exists:roles,name',
        ]);

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        if (! $authUser->hasRole('super-admin') && isset($validated['branch_id'])) {
            if ($authUser->branch_id !== $validated['branch_id']) {
                abort(403, 'Cannot create users for another branch.');
            }
        }

        $role = $validated['role'] ?? null;
        unset($validated['role']);

        $user = User::create($validated);

        if ($role) {
            $user->assignRole($role);
        }

        return response()->json($user->load(['roles', 'permissions']), 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $user->load(['branch', 'roles']);

        return response()->json([
        'user' => $user,
        // 'roles' => $user->getRoleNames(),
        'permissions' => $user->getAllPermissions()->pluck('name'),
    ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'first_name'  => 'sometimes|string|max:255',
            'last_name'   => 'sometimes|string|max:255',
            'phone'       => "nullable|string|unique:users,phone,{$user->id}",
            'bio'         => 'nullable|string',
            'image'       => 'nullable|string',
            'status'      => 'in:active,inactive,suspended',
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete users');

        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return response()->json(['message' => 'User restored successfully.']);
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->syncRoles([$request->role]);

        return response()->json([
            'message' => "Role [{$request->role}] assigned to user [{$user->full_name}].",
            'roles'   => $user->getRoleNames(),
        ]);
    }

    /**
     * Sync direct permissions for a user.
     */
    public function syncPermissions(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $user->syncPermissions($request->permissions);

        return response()->json([
            'message'     => "Permissions updated for user [{$user->full_name}].",
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    /**
     * List all available roles.
     */
    public function roles(): JsonResponse
    {
        $roles = Role::with('permissions')->get();

        return response()->json($roles);
    }

}