<?php
// app/Http/Controllers/Api/DepartmentController.php

namespace App\Http\Controllers\Api;

use App\Models\Department;
use App\Http\Requests\Api\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use Illuminate\Http\Request;

class DepartmentController extends BaseController
{
    /**
     * Display a listing of departments
     */
    public function index(Request $request)
    {
        $departments = Department::with(['organization', 'parent', 'headOfDepartment'])
            ->byOrganization($this->getOrganizationId())
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->parent_id, function ($query, $parentId) {
                if ($parentId === 'null') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $parentId);
                }
            })
            ->when($request->has_children, function ($query) {
                $query->has('children');
            })
            ->orderBy($request->sort_by ?? 'name', $request->sort_direction ?? 'asc')
            ->paginate($request->per_page ?? 15);

        return $this->sendPaginated(DepartmentResource::collection($departments), 'Departments retrieved successfully');
    }

    /**
     * Store a newly created department
     */
    public function store(DepartmentRequest $request)
    {
        $data = $request->validated();
        $data['organization_id'] = $this->getOrganizationId();

        $department = Department::create($data);

        return $this->sendResponse(new DepartmentResource($department), 'Department created successfully', 201);
    }

    /**
     * Display the specified department
     */
    public function show(Department $department)
    {
        return $this->sendResponse(
            new DepartmentResource($department->load(['organization', 'parent', 'children', 'headOfDepartment', 'employees'])),
            'Department retrieved successfully'
        );
    }

    /**
     * Update the specified department
     */
    public function update(DepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());

        return $this->sendResponse(new DepartmentResource($department), 'Department updated successfully');
    }

    /**
     * Remove the specified department
     */
    public function destroy(Department $department)
    {
        // Check if department has children
        if ($department->children()->count() > 0) {
            return $this->sendError('Cannot delete department with sub-departments', [], 422);
        }

        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return $this->sendError('Cannot delete department with associated employees', [], 422);
        }

        $department->delete();

        return $this->sendResponse(null, 'Department deleted successfully');
    }

    /**
     * Get department hierarchy
     */
    public function hierarchy()
    {
        $departments = Department::with(['children' => function ($query) {
                $query->with('children');
            }])
            ->byOrganization($this->getOrganizationId())
            ->whereNull('parent_id')
            ->get();

        return $this->sendResponse(DepartmentResource::collection($departments), 'Department hierarchy retrieved successfully');
    }

    /**
     * Get department employees
     */
    public function employees(Department $department, Request $request)
    {
        $employees = $department->employees()
            ->with(['branch', 'user'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->employment_type, function ($query, $type) {
                $query->where('employment_type', $type);
            })
            ->paginate($request->per_page ?? 15);

        return $this->sendPaginated($employees, 'Department employees retrieved successfully');
    }
}