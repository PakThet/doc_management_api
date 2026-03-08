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
        $this->middleware('permission:view branches')->only(['index', 'show']);
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
                AllowedFilter::exact('organization_id'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('city'),
                AllowedFilter::partial('country'),
            ])
            ->allowedSorts(['name', 'city', 'created_at', 'status'])
            ->allowedIncludes(['organization', 'departments', 'employees']);

        // Branch scope: non-superadmins only see their own branch
        if (! $user->hasRole('super-admin')) {
            $query->forBranch($user->branch_id);
        }

        $branches = $query->paginate(request()->integer('per_page', 15))
            ->appends(request()->query());

        return response()->json($branches);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization_id'  => 'required|exists:organizations,id',
            'name'             => 'required|string|max:255',
            'address'          => 'nullable|string',
            'phone'            => 'nullable|string|max:20',
            'email'            => 'nullable|email',
            'code'             => 'nullable|string|unique:branches,code',
            'city'             => 'nullable|string',
            'state'            => 'nullable|string',
            'country'          => 'nullable|string',
            'postal_code'      => 'nullable|string|max:20',
            'established_date' => 'nullable|date',
            'status'           => 'in:active,inactive',
            'settings'         => 'nullable|array',
        ]);

        $branch = Branch::create($validated);

        return response()->json($branch, 201);
    }

    public function show(Branch $branch): JsonResponse
    {
        $this->authorizeBranchAccess($branch);

        $branch->load(['organization', 'departments', 'employees']);

        return response()->json($branch);
    }

    public function update(Request $request, Branch $branch): JsonResponse
    {
        $this->authorizeBranchAccess($branch);

        $validated = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'address'          => 'nullable|string',
            'phone'            => 'nullable|string|max:20',
            'email'            => 'nullable|email',
            'code'             => "nullable|string|unique:branches,code,{$branch->id}",
            'city'             => 'nullable|string',
            'state'            => 'nullable|string',
            'country'          => 'nullable|string',
            'postal_code'      => 'nullable|string|max:20',
            'established_date' => 'nullable|date',
            'status'           => 'in:active,inactive',
            'settings'         => 'nullable|array',
        ]);

        $branch->update($validated);

        return response()->json($branch);
    }

    public function destroy(Branch $branch): JsonResponse
    {
        $this->authorizeBranchAccess($branch);
        $branch->delete();

        return response()->json(['message' => 'Branch deleted successfully.']);
    }

    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete branches');

        $branch = Branch::withTrashed()->findOrFail($id);
        $branch->restore();

        return response()->json(['message' => 'Branch restored successfully.']);
    }

    /**
     * Ensure non-superadmins can only access their own branch.
     */
    private function authorizeBranchAccess(Branch $branch): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->hasRole('super-admin') && $user->branch_id !== $branch->id) {
            abort(403, 'Access denied to this branch.');
        }
    }
}