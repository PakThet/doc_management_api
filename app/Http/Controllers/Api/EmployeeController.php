<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:view employees')->only(['index', 'show']);
        $this->middleware('permission:create employees')->only(['store']);
        $this->middleware('permission:edit employees')->only(['update', 'toggleStatus']);
        $this->middleware('permission:delete employees')->only(['destroy']);
    }

    public function index(): JsonResponse
    {
        $employees = QueryBuilder::for(Employee::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('department_id'),
                AllowedFilter::exact('branch_id'),
                AllowedFilter::exact('position_id'),
                AllowedFilter::exact('employment_type'),
                AllowedFilter::partial('first_name'),
                AllowedFilter::partial('last_name'),
                AllowedFilter::partial('email'),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['first_name', 'last_name', 'join_date', 'created_at'])
            ->allowedIncludes(['branch', 'department', 'position', 'documents', 'achievements'])
            ->paginate(request()->integer('per_page', 15))
            ->appends(request()->query());

        return response()->json($employees);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id'               => 'required|exists:branches,id',
            'department_id'           => 'nullable|exists:departments,id',
            'position_id'             => 'required|exists:positions,id',
            'employee_code'           => 'required|string|unique:employees',
            'first_name'              => 'required|string|max:255',
            'last_name'               => 'required|string|max:255',
            'email'                   => 'required|email|unique:employees',
            'phone'                   => 'nullable|string|max:20|unique:employees,phone',
            'address'                 => 'nullable|string',
            'status'                  => 'sometimes|in:active,inactive,on_leave,terminated',
            'employment_type'         => 'sometimes|in:full_time,part_time,contract,intern',
            'date_of_birth'           => 'nullable|date',
            'join_date'               => 'required|date',
            'salary'                  => 'nullable|numeric|min:0',
            'emergency_contact_name'  => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'documents.*'             => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $employee = Employee::create($validated);

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('employees/documents', 'public');

                    $employee->documents()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => 'other',
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }

            DB::commit();

            $employee->load(['documents', 'branch', 'department', 'position']);

            return response()->json([
                'message' => 'Employee created successfully',
                'data'    => $employee,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create employee',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee->load(['documents', 'branch', 'department', 'position', 'achievements']);
        return response()->json($employee);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'first_name'              => 'sometimes|string|max:255',
            'last_name'               => 'sometimes|string|max:255',
            'email'                   => "sometimes|email|unique:employees,email,{$employee->id}",
            'phone'                   => "sometimes|nullable|string|max:20|unique:employees,phone,{$employee->id}",
            'address'                 => 'sometimes|nullable|string',
            'status'                  => 'sometimes|in:active,inactive,on_leave,terminated',
            'employment_type'         => 'sometimes|in:full_time,part_time,contract,intern',
            'date_of_birth'           => 'sometimes|nullable|date',
            'join_date'               => 'sometimes|date',
            'salary'                  => 'sometimes|nullable|numeric|min:0',
            'department_id'           => 'sometimes|nullable|exists:departments,id',
            'position_id'             => 'sometimes|exists:positions,id',
            'branch_id'               => 'sometimes|exists:branches,id',
            'emergency_contact_name'  => 'sometimes|nullable|string|max:255',
            'emergency_contact_phone' => 'sometimes|nullable|string|max:20',
            'documents.*'             => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'remove_documents.*'      => 'exists:employee_documents,id',
        ]);

        DB::beginTransaction();

        try {
            $employee->update($validated);

            // Delete requested documents
            if ($request->filled('remove_documents')) {
                foreach ($request->remove_documents as $docId) {
                    $doc = $employee->documents()->find($docId);
                    if ($doc) {
                        Storage::disk('public')->delete($doc->file_path);
                        $doc->delete();
                    }
                }
            }

            // Upload new documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('employees/documents', 'public');

                    $employee->documents()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => 'other',
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }

            DB::commit();

            $employee->load(['documents', 'branch', 'department', 'position']);

            return response()->json([
                'message' => 'Employee updated successfully',
                'data'    => $employee,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update employee',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, Employee $employee): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:active,inactive,on_leave,terminated',
        ]);

        $employee->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Employee status updated successfully',
            'data'    => $employee,
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        // Delete all associated documents from storage
        foreach ($employee->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }
}