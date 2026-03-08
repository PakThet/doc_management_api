<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:view employees')->only(['index', 'show']);
        $this->middleware('permission:create employees')->only(['store']);
        $this->middleware('permission:edit employees')->only(['update']);
        $this->middleware('permission:delete employees')->only(['destroy']);
    }

    public function index(): JsonResponse
    {
        $employees = QueryBuilder::for(Employee::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('department_id'),
                AllowedFilter::exact('employment_type'),
                AllowedFilter::partial('first_name'),
                AllowedFilter::partial('last_name'),
                AllowedFilter::partial('email'),
                AllowedFilter::partial('position'),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts([
                'first_name',
                'last_name',
                'join_date',
                'created_at',
                'status',
                'position'
            ])
            ->allowedIncludes(['branch', 'department', 'managedDepartments'])
            ->paginate(request()->integer('per_page', 15))
            ->appends(request()->query());

        return response()->json($employees);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Employee::class);

        $validated = $request->validate([
            'branch_id'                => 'required|exists:branches,id',
            'department_id'            => 'nullable|exists:departments,id',
            'employee_code'            => 'required|string|unique:employees,employee_code',
            'first_name'               => 'required|string|max:255',
            'last_name'                => 'required|string|max:255',
            'email'                    => 'required|email|unique:employees,email',
            'phone'                    => 'nullable|string|max:20|unique:employees,phone',
            'address'                  => 'nullable|string',
            'status'                   => 'in:active,inactive,terminated,on_leave',
            'employment_type'          => 'in:full_time,part_time,contract,intern,temporary',
            'date_of_birth'            => 'nullable|date',
            'join_date'                => 'required|date',
            'probation_end_date'       => 'nullable|date',
            'confirmation_date'        => 'nullable|date',
            'resignation_date'         => 'nullable|date',
            'exit_date'                => 'nullable|date',
            'position'                 => 'required|string|max:255',
            'salary'                   => 'nullable|numeric|min:0',
            'emergency_contact_name'   => 'nullable|string',
            'emergency_contact_phone'  => 'nullable|string|max:20',
            'bank_details'             => 'nullable|array',
            'documents'                => 'nullable|array',
            'metadata'                 => 'nullable|array',
        ]);

        $employee = Employee::create($validated);

        return response()->json($employee, 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);

        $employee->load(['branch', 'department', 'managedDepartments']);

        return response()->json($employee);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('update', $employee);

        $validated = $request->validate([
            'department_id'            => 'nullable|exists:departments,id',
            'first_name'               => 'sometimes|string|max:255',
            'last_name'                => 'sometimes|string|max:255',
            'email'                    => "sometimes|email|unique:employees,email,{$employee->id}",
            'phone'                    => "nullable|string|max:20|unique:employees,phone,{$employee->id}",
            'address'                  => 'nullable|string',
            'status'                   => 'in:active,inactive,terminated,on_leave',
            'employment_type'          => 'in:full_time,part_time,contract,intern,temporary',
            'date_of_birth'            => 'nullable|date',
            'join_date'                => 'sometimes|date',
            'probation_end_date'       => 'nullable|date',
            'confirmation_date'        => 'nullable|date',
            'resignation_date'         => 'nullable|date',
            'exit_date'                => 'nullable|date',
            'position'                 => 'sometimes|string|max:255',
            'salary'                   => 'nullable|numeric|min:0',
            'emergency_contact_name'   => 'nullable|string',
            'emergency_contact_phone'  => 'nullable|string|max:20',
            'bank_details'             => 'nullable|array',
            'documents'                => 'nullable|array',
            'metadata'                 => 'nullable|array',
        ]);

        $employee->update($validated);

        return response()->json($employee);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $this->authorize('delete', $employee);

        return response()->json(['message' => 'Employee deleted successfully.']);
    }

    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete employees');

        $employee = Employee::withTrashed()->findOrFail($id);
        $employee->restore();

        return response()->json(['message' => 'Employee restored successfully.']);
    }

}