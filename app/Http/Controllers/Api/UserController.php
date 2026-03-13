<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        $this->middleware('permission:edit users')->only(['update', 'assignRole']);
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
            'role'        => 'required|string|exists:roles,name',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        if (! $authUser->hasRole('super-admin')) {
            $validated['branch_id'] = $authUser->branch_id;
        }
        if ($request->role === 'super-admin' && ! $authUser->hasRole('super-admin')) {
            return response()->json([
                'message' => 'Only Super Admin can create another Super Admin.'
            ], 403);
        }
        if (isset($validated['branch_id']) && ! $authUser->hasRole('super-admin') && $validated['branch_id'] !== $authUser->branch_id) {
            abort(403, 'Cannot create users for another branch.');
        }

        $validated['password'] = bcrypt($validated['password']);
        $role = $validated['role'] ?? null;
        unset($validated['role']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

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
            'user'        => $user,
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        if ($user->hasRole('super-admin') && ! $authUser->hasRole('super-admin')) {
            return response()->json([
                'message' => 'You cannot modify a Super Admin.'
            ], 403);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name'  => 'sometimes|string|max:255',
            'phone'      => "nullable|string|unique:users,phone,{$user->id}",
            'bio'        => 'nullable|string',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'     => 'in:active,inactive,suspended',
        ]);

        if ($request->hasFile('image')) {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        $user->update($validated);

        return response()->json($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if ($authUser->id === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.'
            ], 403);
        }
        if ($user->hasRole('super-admin') && ! $authUser->hasRole('super-admin')) {
            return response()->json([
                'message' => 'You cannot delete a Super Admin.'
            ], 403);
        }

        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }

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

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        $role = $request->role;

        // Only super-admin can assign super-admin role
        if ($role === 'super-admin' && ! $authUser->hasRole('super-admin')) {
            return response()->json([
                'message' => 'Only Super Admin can assign the Super Admin role.'
            ], 403);
        }

        if ($authUser->id === $user->id) {
            return response()->json([
                'message' => 'You cannot change your own role.'
            ], 403);
        }

        $user->syncRoles([$request->role]);

        return response()->json([
            'message' => "Role [{$request->role}] assigned to user [{$user->full_name}].",
            'roles'   => $user->getRoleNames(),
        ]);
    }
}
