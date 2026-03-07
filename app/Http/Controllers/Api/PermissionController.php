<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PermissionRequest;
use App\Models\Permission;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PermissionController extends BaseController
{
    public function index(Request $request)
    {
        $permissions = QueryBuilder::for(Permission::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('guard_name'),
            ])
            ->allowedSorts(['id', 'name', 'guard_name', 'created_at'])
            ->defaultSort('name')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->sendPaginated($permissions, $permissions->items(), 'Permissions retrieved successfully');
    }

    public function store(PermissionRequest $request)
    {
        $permission = Permission::create($request->validated());

        return $this->sendResponse($permission, 'Permission created successfully', 201);
    }

    public function show(Permission $permission)
    {
        return $this->sendResponse($permission, 'Permission retrieved successfully');
    }

    public function update(PermissionRequest $request, Permission $permission)
    {
        $permission->update($request->validated());

        return $this->sendResponse($permission, 'Permission updated successfully');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return $this->sendResponse(null, 'Permission deleted successfully');
    }
}
