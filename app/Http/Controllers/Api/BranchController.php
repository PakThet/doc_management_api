<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use App\Http\Requests\Api\BranchRequest;
use App\Http\Resources\BranchResource;
use App\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class BranchController extends BaseController
{
    /**
     * Display a listing of branches
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $allowedFilters = [
            AllowedFilter::partial('name'),
            AllowedFilter::partial('code'),
            AllowedFilter::partial('city'),
            AllowedFilter::partial('country'),
            AllowedFilter::exact('status'),
        ];

        if ($user->organization_id === null) {
            $allowedFilters[] = AllowedFilter::exact('organization_id');
        }

        $branches = QueryBuilder::for(Branch::class)
            ->allowedFilters($allowedFilters)

            ->allowedSorts([
                'name',
                'city',
                'created_at'
            ])

            ->allowedIncludes([
                'organization',
                'employees'
            ])

            ->defaultSort('name')

            ->paginate($request->per_page ?? 15)
            ->appends($request->query());

        return $this->sendPaginated(
            BranchResource::collection($branches),
            'Branches retrieved successfully'
        );
    }

    public function store(BranchRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();

        if ($user->organization_id !== null) {
            $data['organization_id'] = $user->organization_id;
        }

        $branch = Branch::create($data);

        return $this->sendResponse(
            new BranchResource($branch),
            'Branch created successfully',
            201
        );
    }

    public function show(Branch $branch)
    {
        return $this->sendResponse(
            new BranchResource(
                $branch->load(['organization', 'employees', 'documents'])
            ),
            'Branch retrieved successfully'
        );
    }

    public function update(BranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        return $this->sendResponse(
            new BranchResource($branch),
            'Branch updated successfully'
        );
    }

    public function destroy(Branch $branch)
    {
        if ($branch->employees()->exists()) {
            return $this->sendError(
                'Cannot delete branch with associated employees.',
                [],
                422
            );
        }

        $branch->delete();

        return $this->sendResponse(
            null,
            'Branch deleted successfully'
        );
    }

    public function employees(Branch $branch, Request $request)
    {
        $employees = QueryBuilder::for($branch->employees())
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('department_id'),
            ])
            ->allowedIncludes([
                'department',
                'user'
            ])
            ->paginate($request->per_page ?? 15)
            ->appends($request->query());

        return $this->sendPaginated(
            EmployeeResource::collection($employees),
            'Branch employees retrieved successfully'
        );
    }
}