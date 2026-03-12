<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


// ─────────────────────────────────────────────────────────────────────────────
// PermissionController
// ─────────────────────────────────────────────────────────────────────────────

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super-admin');
    }

    public function index(): JsonResponse
    {
        $permissions = QueryBuilder::for(Permission::class)
            ->allowedFilters([AllowedFilter::partial('name')])
            ->allowedSorts(['name', 'created_at'])
            ->paginate(request()->integer('per_page', 15));

        return response()->json($permissions);
    }

    // public function store(Request $request): JsonResponse
    // {
    //     $validated = $request->validate([
    //         'name'       => 'required|string|unique:permissions,name',
    //         'guard_name' => 'nullable|string',
    //     ]);

    //     $permission = Permission::create([
    //         'name'       => $validated['name'],
    //         'guard_name' => $validated['guard_name'] ?? 'api',
    //     ]);

    //     return response()->json($permission, 201);
    // }

    // public function destroy(Permission $permission): JsonResponse
    // {
    //     $permission->delete();

    //     return response()->json(['message' => 'Permission deleted successfully.']);
    // }
}
