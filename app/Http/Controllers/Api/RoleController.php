<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RoleController extends BaseController
{
    public function index(Request $request)
    {
        $roles = QueryBuilder::for(Role::class)
            ->with(['permissions', 'branch'])
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('guard_name'),
                AllowedFilter::exact('branch_id'),
            ])
            ->allowedSorts(['id', 'name', 'guard_name', 'created_at'])
            ->defaultSort('name')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->sendPaginated($roles, $roles->items(), 'Roles retrieved successfully');
    }

    public function store(RoleRequest $request)
    {
        $role = Role::create($request->validated());

        return $this->sendResponse($role->load(['permissions', 'branch']), 'Role created successfully', 201);
    }

    public function show(Role $role)
    {
        return $this->sendResponse($role->load(['permissions', 'branch']), 'Role retrieved successfully');
    }

    public function update(RoleRequest $request, Role $role)
    {
        $role->update($request->validated());

        return $this->sendResponse($role->load(['permissions', 'branch']), 'Role updated successfully');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return $this->sendResponse(null, 'Role deleted successfully');
    }

    public function assignPermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', Rule::exists('permissions', 'name')],
        ]);

        $permissionNames = Permission::query()
            ->whereIn('name', $validated['permissions'])
            ->pluck('name');

        $role->syncPermissions($permissionNames);

        return $this->sendResponse($role->load('permissions'), 'Permissions assigned successfully');
    }
}
