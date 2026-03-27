<?php
// app/Http/Controllers/Api/PermissionController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends BaseController
{
    /**
     * Display a listing of permissions
     */
    public function index(Request $request)
    {
        $organizationId = Auth::user()->organization_id;
        
        $permissionsQuery = Permission::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->group, function ($query, $group) {
                $query->where('name', 'like', "{$group}%");
            })
            ->where(function ($query) use ($organizationId) {
                $query->whereNull('organization_id')
                    ->orWhere('organization_id', $organizationId);
            })
            ->withCount('roles')
            ->orderBy($request->sort_by ?? 'name', $request->sort_direction ?? 'asc');

        if ($request->query('all')) {
            $permissions = $permissionsQuery->get();
            return $this->sendResponse(PermissionResource::collection($permissions), 'All permissions retrieved successfully');
        }

        $permissions = $permissionsQuery->paginate($request->per_page ?? 15);
        return $this->sendPaginated(PermissionResource::collection($permissions), 'Permissions retrieved successfully');
    }

    public function getPermissions($userId)
{
    $user = \App\Models\User::findOrFail($userId);

    if ($user->organization_id !== Auth::user()->organization_id) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $permissions = $user->getAllPermissions();

    return response()->json([
        'user' => $user->only(['id','first_name','last_name','email']),
        'permissions' => $permissions
    ]);
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