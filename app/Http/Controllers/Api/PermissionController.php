<?php
// app/Http/Controllers/Api/PermissionController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends BaseController
{
    /**
     * Display a listing of permissions
     */
    public function index(Request $request)
    {
        $permissions = Permission::when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->group, function ($query, $group) {
                $query->where('name', 'like', "{$group}%");
            })
            ->orderBy($request->sort_by ?? 'name', $request->sort_direction ?? 'asc')
            ->paginate($request->per_page ?? 15);

        return $this->sendPaginated($permissions, 'Permissions retrieved successfully');
    }

    /**
     * Get permission groups
     */
    public function groups()
    {
        $permissions = Permission::all();
        
        $groups = $permissions->groupBy(function ($permission) {
            return explode(' ', $permission->name)[1] ?? 'other';
        })->map(function ($groupPermissions, $group) {
            return [
                'name' => $group,
                'permissions' => $groupPermissions->pluck('name')->values()
            ];
        })->values();

        return $this->sendResponse($groups, 'Permission groups retrieved successfully');
    }
}