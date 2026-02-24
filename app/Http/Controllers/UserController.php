<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class UserController extends Controller
{
    // =============================
    // INDEX (WITH FILTER + PAGINATION)
    // =============================
    public function index()
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters([
                'first_name',
                'last_name',
                'email',
                'status',
                AllowedFilter::exact('id'),
            ])
            ->allowedSorts([
                'first_name',
                'last_name',
                'created_at'
            ])
            ->with('roles')
            ->paginate(10);

        return response()->json($users);
    }

    // =============================
    // STORE
    // =============================
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:6',
            'phone'      => 'nullable|string|unique:users,phone',
            'image'      => 'nullable|image|max:2048',
            'status'     => 'required|in:active,inactive,suspended',
            'role'       => 'required|exists:roles,name',
        ]);

        $data = $request->all();

        $data['password'] = Hash::make($request->password);

        // Upload Image
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('users', 'public');
        }

        $user = User::create($data);

        // Assign Role
        $user->assignRole($request->role);

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user->load('roles')
        ], 201);
    }

    // =============================
    // SHOW
    // =============================
    public function show(User $user)
    {
        return response()->json(
            $user->load('roles')
        );
    }

    // =============================
    // UPDATE
    // =============================
    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name'  => 'sometimes|required|string|max:255',
            'email'      => 'sometimes|required|email|unique:users,email,'.$user->id,
            'phone'      => 'nullable|string|unique:users,phone,'.$user->id,
            'password'   => 'nullable|min:6',
            'image'      => 'nullable|image|max:2048',
            'status'     => 'sometimes|in:active,inactive,suspended',
            'role'       => 'nullable|exists:roles,name',
        ]);

        $data = $request->except(['password','image','role']);

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }

            $data['image'] = $request->file('image')
                ->store('users', 'public');
        }

        $user->update($data);

        // Update Role (if provided)
        if ($request->role) {
            $user->syncRoles([$request->role]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user->load('roles')
        ]);
    }

    // =============================
    // DELETE
    // =============================
    public function destroy(User $user)
    {
        if ($user->image) {
            Storage::disk('public')->delete($user->image);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}