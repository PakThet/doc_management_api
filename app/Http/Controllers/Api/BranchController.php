<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view branches')->only(['index','show']);
        $this->middleware('permission:create branches')->only(['store']);
        $this->middleware('permission:edit branches')->only(['update']);
        $this->middleware('permission:delete branches')->only(['destroy']);
    }

    public function index(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = QueryBuilder::for(Branch::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('city'),
                AllowedFilter::partial('country'),
                AllowedFilter::exact('head_of_branch_id'),
            ])
            ->allowedSorts([
                'name',
                'city',
                'created_at',
                'status'
            ])
            ->allowedIncludes([
                'departments',
                'employees',
                'users',
                'documents',
                'headOfBranch'
            ]);

        // Non super-admin only see their branch
        if (! $user->hasRole('super-admin')) {
            $query->forBranch($user->branch_id);
        }

        $branches = $query
            ->paginate(request()->integer('per_page',15))
            ->appends(request()->query());

        return response()->json($branches);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'head_of_branch_id' => 'nullable|exists:employees,id',
            'name'              => 'required|string|max:255',
            'address'           => 'nullable|string',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email',
            'code'              => 'nullable|string|max:50|unique:branches,code',
            'city'              => 'nullable|string|max:255',
            'state'             => 'nullable|string|max:255',
            'country'           => 'nullable|string|max:255',
            'postal_code'       => 'nullable|string|max:20',
            'established_date'  => 'nullable|date',
            'status'            => 'in:active,inactive'
        ]);

        $branch = Branch::create($validated);

        return response()->json($branch,201);
    }

    public function show(Branch $branch): JsonResponse
    {
        $this->authorizeBranchAccess($branch);

        $branch->load([
            'departments',
            'employees',
            'users',
            'documents',
            'headOfBranch'
        ]);
        $employeeCount = $branch->employees->count();

        return response()->json([
            'message' => true,
            'employee_count' => $employeeCount,
            'branch' => $branch,
        ]);
    }

    public function update(Request $request, Branch $branch): JsonResponse
    {
        $this->authorizeBranchAccess($branch);

        $validated = $request->validate([
            'head_of_branch_id' => 'nullable|exists:employees,id',
            'name'              => 'sometimes|string|max:255',
            'address'           => 'nullable|string',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email',
            'code'              => "nullable|string|max:50|unique:branches,code,{$branch->id}",
            'city'              => 'nullable|string|max:255',
            'state'             => 'nullable|string|max:255',
            'country'           => 'nullable|string|max:255',
            'postal_code'       => 'nullable|string|max:20',
            'established_date'  => 'nullable|date',
            'status'            => 'in:active,inactive'
        ]);

        $branch->update($validated);

        return response()->json($branch);
    }

    public function destroy(Branch $branch): JsonResponse
    {
        $this->authorizeBranchAccess($branch);

        $branch->delete();

        return response()->json([
            'message' => 'Branch deleted successfully.'
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete branches');

        $branch = Branch::withTrashed()->findOrFail($id);
        $branch->restore();

        return response()->json([
            'message' => 'Branch restored successfully.'
        ]);
    }

    private function authorizeBranchAccess(Branch $branch): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->hasRole('super-admin') && $user->branch_id !== $branch->id) {
            abort(403,'Access denied to this branch.');
        }
    }
}