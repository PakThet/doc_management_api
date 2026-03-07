<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        $users = QueryBuilder::for(User::class)
            ->with(['branch', 'roles'])
            ->allowedFilters([
                AllowedFilter::partial('first_name'),
                AllowedFilter::partial('last_name'),
                AllowedFilter::partial('email'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('branch_id'),
            ])
            ->allowedSorts(['id', 'first_name', 'last_name', 'email', 'created_at'])
            ->defaultSort('-created_at')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->sendPaginated(
            $users,
            UserResource::collection($users->items()),
            'Users retrieved successfully'
        );
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        if ($request->filled('roles')) {
            $user->syncRoles($request->input('roles'));
        }

        return $this->sendResponse(
            new UserResource($user->load(['branch', 'roles'])),
            'User created successfully',
            201
        );
    }

    public function show(User $user)
    {
        return $this->sendResponse(
            new UserResource($user->load(['branch', 'roles', 'permissions'])),
            'User retrieved successfully'
        );
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
            $data['password_changed_at'] = Carbon::now();
        } else {
            unset($data['password']);
        }

        $user->update($data);

        if ($request->has('roles')) {
            $user->syncRoles($request->input('roles', []));
        }

        return $this->sendResponse(
            new UserResource($user->load(['branch', 'roles'])),
            'User updated successfully'
        );
    }

    public function destroy(User $user)
    {
        $user->delete();

        return $this->sendResponse(null, 'User deleted successfully');
    }
}
