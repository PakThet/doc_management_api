<?php
// app/Http/Controllers/Api/EmployeeController.php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Http\Requests\Api\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;

class EmployeeController extends BaseController
{
    /**
     * Display a listing of employees
     */
    public function index(Request $request)
    {
        $employees = Employee::with(['organization', 'branch', 'department', 'user'])
            ->byOrganization($this->getOrganizationId())
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->employment_type, function ($query, $type) {
                $query->where('employment_type', $type);
            })
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->department_id, function ($query, $departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->when($request->join_date_from, function ($query, $date) {
                $query->whereDate('join_date', '>=', $date);
            })
            ->when($request->join_date_to, function ($query, $date) {
                $query->whereDate('join_date', '<=', $date);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_direction ?? 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->sendPaginated(EmployeeResource::collection($employees), 'Employees retrieved successfully');
    }

    /**
     * Store a newly created employee
     */
    public function store(EmployeeRequest $request)
    {
        $data = $request->validated();
        if (!isset($data['organization_id'])) { $data['organization_id'] = $this->getOrganizationId(); }

        $employee = Employee::create($data);

        return $this->sendResponse(new EmployeeResource($employee), 'Employee created successfully', 201);
    }

    /**
     * Display the specified employee
     */
    public function show(Employee $employee)
    {
        return $this->sendResponse(
            new EmployeeResource($employee->load(['organization', 'branch', 'department', 'user', 'headedDepartment', 'documents']\)),
            'Employee retrieved successfully'
        );
    }

    /**
     * Update the specified employee
     */
    public function update(EmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return $this->sendResponse(new EmployeeResource($employee), 'Employee updated successfully');
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Employee $employee)
    {
        // Check if employee is head of department
        if ($employee->headedDepartment()->exists()) {
            return $this->sendError('Cannot delete employee who is head of a department. Please reassign department head first.', [], 422);
        }

        $employee->delete();

        // Also delete associated user if exists
        if ($employee->user) {
            $employee->user->delete();
        }

        return $this->sendResponse(null, 'Employee deleted successfully');
    }

    /**
     * Link employee to user account
     */
    public function linkUser(Request $request, Employee $employee)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // Check if user is already linked to another employee
        $existingEmployee = Employee::where('user_id', $request->user_id)->first();
        if ($existingEmployee && $existingEmployee->id !== $employee->id) {
            return $this->sendError('This user is already linked to another employee', [], 422);
        }

        $employee->update(['user_id' => $request->user_id]);

        return $this->sendResponse(new EmployeeResource($employee), 'User linked to employee successfully');
    }

    /**
     * Unlink user from employee
     */
    public function unlinkUser(Employee $employee)
    {
        $employee->update(['user_id' => null]);

        return $this->sendResponse(new EmployeeResource($employee), 'User unlinked from employee successfully');
    }

    /**
     * Get employee statistics
     */
    public function statistics()
    {
        $organizationId = $this->getOrganizationId();

        $stats = [
            'total' => Employee::byOrganization($organizationId)->count(),
            'active' => Employee::byOrganization($organizationId)->where('status', 'active')->count(),
            'on_leave' => Employee::byOrganization($organizationId)->where('status', 'on_leave')->count(),
            'terminated' => Employee::byOrganization($organizationId)->where('status', 'terminated')->count(),
            'by_type' => Employee::byOrganization($organizationId)
                ->selectRaw('employment_type, count(*) as count')
                ->groupBy('employment_type')
                ->pluck('count', 'employment_type'),
            'by_branch' => Employee::byOrganization($organizationId)
                ->selectRaw('branch_id, count(*) as count')
                ->with('branch:id,name')
                ->groupBy('branch_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'branch' => $item->branch->name ?? 'Unknown',
                        'count' => $item->count
                    ];
                }),
            'by_department' => Employee::byOrganization($organizationId)
                ->selectRaw('department_id, count(*) as count')
                ->with('department:id,name')
                ->groupBy('department_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'department' => $item->department->name ?? 'Unknown',
                        'count' => $item->count
                    ];
                }),
            'new_hires_this_month' => Employee::byOrganization($organizationId)
                ->whereMonth('join_date', now()->month)
                ->whereYear('join_date', now()->year)
                ->count(),
            'birthdays_this_month' => Employee::byOrganization($organizationId)
                ->whereMonth('date_of_birth', now()->month)
                ->count(),
        ];

        return $this->sendResponse($stats, 'Employee statistics retrieved successfully');
    }
}



