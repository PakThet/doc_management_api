<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeController extends BaseController
{
    public function index(Request $request)
    {
        $employees = QueryBuilder::for(Employee::class)
            ->with(['branch', 'department'])
            ->allowedFilters([
                AllowedFilter::partial('employee_code'),
                AllowedFilter::partial('first_name'),
                AllowedFilter::partial('last_name'),
                AllowedFilter::partial('email'),
                AllowedFilter::exact('branch_id'),
                AllowedFilter::exact('department_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('employment_type'),
            ])
            ->allowedSorts(['id', 'employee_code', 'first_name', 'last_name', 'join_date', 'created_at'])
            ->defaultSort('-created_at')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->sendPaginated(
            $employees,
            EmployeeResource::collection($employees->items()),
            'Employees retrieved successfully'
        );
    }

    public function store(EmployeeRequest $request)
    {
        $employee = Employee::create($request->validated());

        return $this->sendResponse(new EmployeeResource($employee), 'Employee created successfully', 201);
    }

    public function show(Employee $employee)
    {
        $employee->load(['branch', 'department', 'headedDepartments']);

        return $this->sendResponse(new EmployeeResource($employee), 'Employee retrieved successfully');
    }

    public function update(EmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return $this->sendResponse(new EmployeeResource($employee), 'Employee updated successfully');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return $this->sendResponse(null, 'Employee deleted successfully');
    }
}
