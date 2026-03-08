<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view departments')->only(['index', 'show']);
        $this->middleware('permission:create departments')->only(['store']);
        $this->middleware('permission:edit departments')->only(['update']);
        $this->middleware('permission:delete departments')->only(['destroy']);
    }

    public function index(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = QueryBuilder::for(Department::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('branch_id'),
                AllowedFilter::exact('parent_id'),
                AllowedFilter::partial('name'),
            ])
            ->allowedSorts(['name', 'created_at', 'status'])
            ->allowedIncludes(['branch', 'parent', 'children', 'headOfDepartment', 'employees', 'documentPrefixes']);

        // Branch scope
        if (! $user->hasRole('super-admin') && $user->branch_id) {
            $query->forBranch($user->branch_id);
        }

        $departments = $query->paginate(request()->integer('per_page', 15))
            ->appends(request()->query());

        return response()->json($departments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id'              => 'required|exists:branches,id',
            'name'                   => 'required|string|max:255',
            'code'                   => 'nullable|string|unique:departments,code',
            'description'            => 'nullable|string',
            'parent_id'              => 'nullable|exists:departments,id',
            'head_of_department_id'  => 'nullable|exists:employees,id',
            'email'                  => 'nullable|email',
            'phone'                  => 'nullable|string|max:20',
            'location'               => 'nullable|string',
            'budget'                 => 'nullable|numeric|min:0',
            'status'                 => 'in:active,inactive',
            'metadata'               => 'nullable|array',
        ]);

        $this->authorizeBranchAccess($validated['branch_id']);

        $department = Department::create($validated);

        return response()->json($department, 201);
    }

    public function show(Department $department): JsonResponse
    {
        $this->authorizeBranchAccess($department->branch_id);

        $department->load(['branch', 'parent', 'children', 'headOfDepartment', 'employees']);

        return response()->json($department);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $this->authorizeBranchAccess($department->branch_id);

        $validated = $request->validate([
            'name'                   => 'sometimes|string|max:255',
            'code'                   => "nullable|string|unique:departments,code,{$department->id}",
            'description'            => 'nullable|string',
            'parent_id'              => 'nullable|exists:departments,id',
            'head_of_department_id'  => 'nullable|exists:employees,id',
            'email'                  => 'nullable|email',
            'phone'                  => 'nullable|string|max:20',
            'location'               => 'nullable|string',
            'budget'                 => 'nullable|numeric|min:0',
            'status'                 => 'in:active,inactive',
            'metadata'               => 'nullable|array',
        ]);

        $department->update($validated);

        return response()->json($department);
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->authorizeBranchAccess($department->branch_id);
        $department->delete();

        return response()->json(['message' => 'Department deleted successfully.']);
    }

    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete departments');

        $department = Department::withTrashed()->findOrFail($id);
        $department->restore();

        return response()->json(['message' => 'Department restored successfully.']);
    }

    private function authorizeBranchAccess(int $branchId): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->hasRole('super-admin') && $user->branch_id !== $branchId) {
            abort(403, 'Access denied to this branch.');
        }
    }
}