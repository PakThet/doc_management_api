<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DepartmentController extends BaseController
{
    public function index(Request $request)
    {
        $departments = QueryBuilder::for(Department::class)
            ->with(['branch', 'parent', 'headOfDepartment'])
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('branch_id'),
                AllowedFilter::exact('parent_id'),
                AllowedFilter::exact('head_of_department_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::partial('code'),
            ])
            ->allowedSorts(['id', 'name', 'code', 'created_at'])
            ->defaultSort('-created_at')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->sendPaginated(
            $departments,
            DepartmentResource::collection($departments->items()),
            'Departments retrieved successfully'
        );
    }

    public function store(DepartmentRequest $request)
    {
        $department = Department::create($request->validated());

        return $this->sendResponse(new DepartmentResource($department), 'Department created successfully', 201);
    }

    public function show(Department $department)
    {
        $department->load(['branch', 'parent', 'children', 'headOfDepartment'])->loadCount('employees');

        return $this->sendResponse(new DepartmentResource($department), 'Department retrieved successfully');
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());

        return $this->sendResponse(new DepartmentResource($department), 'Department updated successfully');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return $this->sendResponse(null, 'Department deleted successfully');
    }
}
