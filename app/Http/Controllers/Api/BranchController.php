<?php
// app/Http/Controllers/Api/BranchController.php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use App\Http\Requests\Api\BranchRequest;
use App\Http\Resources\BranchResource;
use Illuminate\Http\Request;

class BranchController extends BaseController
{
    /**
     * Display a listing of branches
     */
    public function index(Request $request)
    {
        $branches = Branch::with('organization')
            ->byOrganization($this->getOrganizationId())
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->city, function ($query, $city) {
                $query->where('city', $city);
            })
            ->when($request->country, function ($query, $country) {
                $query->where('country', $country);
            })
            ->orderBy($request->sort_by ?? 'name', $request->sort_direction ?? 'asc')
            ->paginate($request->per_page ?? 15);

        return $this->sendPaginated(BranchResource::collection($branches), 'Branches retrieved successfully');
    }

    /**
     * Store a newly created branch
     */
    public function store(BranchRequest $request)
    {
        $data = $request->validated();
        $data['organization_id'] = $this->getOrganizationId();

        $branch = Branch::create($data);

        return $this->sendResponse(new BranchResource($branch), 'Branch created successfully', 201);
    }

    /**
     * Display the specified branch
     */
    public function show(Branch $branch)
    {
        return $this->sendResponse(
            new BranchResource($branch->load(['organization', 'employees', 'documents'])),
            'Branch retrieved successfully'
        );
    }

    /**
     * Update the specified branch
     */
    public function update(BranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        return $this->sendResponse(new BranchResource($branch), 'Branch updated successfully');
    }

    /**
     * Remove the specified branch
     */
    public function destroy(Branch $branch)
    {
        // Check if branch has employees
        if ($branch->employees()->count() > 0) {
            return $this->sendError('Cannot delete branch with associated employees', [], 422);
        }

        $branch->delete();

        return $this->sendResponse(null, 'Branch deleted successfully');
    }

    /**
     * Get branch employees
     */
    public function employees(Branch $branch, Request $request)
    {
        $employees = $branch->employees()
            ->with(['department', 'user'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->department_id, function ($query, $departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->paginate($request->per_page ?? 15);

        return $this->sendPaginated($employees, 'Branch employees retrieved successfully');
    }
}
