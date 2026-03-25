<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view users')->only(['index', 'show']);
        $this->middleware('permission:create users')->only(['store']);
        $this->middleware('permission:edit users')->only(['update', 'assignRole']);
        $this->middleware('permission:delete users')->only(['destroy']);
    }

    // ─── List ─────────────────────────────────────────────────────────────────

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
            ->allowedIncludes(['branch', 'roles']);

        // ✅ Non-super-admins only see users in their own branch
        if (! $authUser->hasRole('super-admin')) {
            $query->where('branch_id', $authUser->branch_id);
        }

        $users = $query
            ->paginate(request()->integer('per_page'))
            ->appends(request()->query());

        return response()->json($users);
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id'  => 'nullable|exists:branches,id',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|unique:users,phone',
            'bio'        => 'nullable|string',
            'status'     => 'in:active,inactive,suspended',
            'password'   => 'required|string|min:6|confirmed',
            'role'       => 'required|string|exists:roles,name',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        // Non-super-admin always creates in their own branch
        if (! $authUser->hasRole('super-admin')) {
            $validated['branch_id'] = $authUser->branch_id;
        }

        if ($validated['role'] === 'super-admin' && ! $authUser->hasRole('super-admin')) {
            return $this->sendError('Only Super Admin can create another Super Admin.', [], 403);
        }

        if (
            isset($validated['branch_id']) &&
            ! $authUser->hasRole('super-admin') &&
            $validated['branch_id'] !== $authUser->branch_id
        ) {
            return $this->sendError('Cannot create users for another branch.', [], 403);
        }

        $role = $validated['role'];
        unset($validated['role']);

        $validated['password'] = bcrypt($validated['password']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        $user = User::create($validated);
        $user->assignRole($role);

        return $this->sendResponse(
            $user->load(['branch', 'roles']),
            'User created successfully.',
            201,
        );
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return $this->sendResponse(
            $user->load(['branch', 'roles']),
            'User fetched successfully.',
        );
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        // ✅ Prevent any non-super-admin from editing a super-admin
        if ($user->hasRole('super-admin') && ! $authUser->hasRole('super-admin')) {
            return $this->sendError('You cannot modify a Super Admin.', [], 403);
        }

        // ✅ Prevent editing own account via this endpoint
        // (use a dedicated profile endpoint for self-edits)
        if ($authUser->id === $user->id) {
            return $this->sendError('You cannot edit your own account here. Use the profile settings instead.', [], 403);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name'  => 'sometimes|string|max:255',
            'phone'      => "nullable|string|unique:users,phone,{$user->id}",
            'bio'        => 'nullable|string',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'     => 'in:active,inactive,suspended',
            'branch_id'  => 'sometimes|nullable|exists:branches,id',
        ]);

        // ✅ Non-super-admins cannot change branch — force it to their own
        if (! $authUser->hasRole('super-admin')) {
            $validated['branch_id'] = $authUser->branch_id;
        }

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        $user->update($validated);

        return $this->sendResponse(
            $user->fresh()->load(['branch', 'roles']),
            'User updated successfully.',
        );
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        // ✅ Prevent self-deletion
        if ($authUser->id === $user->id) {
            return $this->sendError('You cannot delete your own account.', [], 403);
        }

        // ✅ Prevent non-super-admin from deleting a super-admin
        if ($user->hasRole('super-admin') && ! $authUser->hasRole('super-admin')) {
            return $this->sendError('You cannot delete a Super Admin.', [], 403);
        }

        // ✅ Prevent non-super-admin from deleting users outside their branch
        if (! $authUser->hasRole('super-admin') && $user->branch_id !== $authUser->branch_id) {
            return $this->sendError('You cannot delete users from another branch.', [], 403);
        }

        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }

        $user->delete();

        return $this->sendResponse(null, 'User deleted successfully.');
    }

    // ─── Restore ──────────────────────────────────────────────────────────────

    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete users');

        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return $this->sendResponse(null, 'User restored successfully.');
    }

    // ─── Assign role ──────────────────────────────────────────────────────────

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        $role     = $request->role;

        // ✅ Only super-admin can assign super-admin role
        if ($role === 'super-admin' && ! $authUser->hasRole('super-admin')) {
            return $this->sendError('Only Super Admin can assign the Super Admin role.', [], 403);
        }

        // ✅ Cannot change your own role
        if ($authUser->id === $user->id) {
            return $this->sendError('You cannot change your own role.', [], 403);
        }

        $user->syncRoles([$role]);

        return $this->sendResponse(
            ['roles' => $user->getRoleNames()],
            "Role [{$role}] assigned to user [{$user->full_name}].",
        );
    }
}