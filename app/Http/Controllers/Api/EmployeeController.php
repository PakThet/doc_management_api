<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Achievement;
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
        $this->middleware('permission:edit employees')->only(['update']);
        $this->middleware('permission:delete employees')->only(['destroy']);
    }

    // List employees
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
            ->allowedSorts(['first_name', 'last_name', 'join_date', 'created_at', 'status', 'position'])
            ->allowedIncludes([
                'branch',
                'department',
                // 'documents', 
                // 'achievements'
            ])
            ->paginate(request()->integer('per_page', 15))
            ->appends(request()->query());

        return response()->json($employees);
    }

    // Create employee
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'employee_code' => 'required|string|unique:employees',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees',
            'phone' => 'nullable|string|max:20|unique:employees,phone',
            'join_date' => 'required|date',
            'position' => 'required|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,terminated,on_leave',
            'employment_type' => 'nullable|in:full_time,part_time,contract,intern,temporary',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'achievements' => 'nullable|array',
            'achievements.*.title' => 'required|string|max:255',
            'achievements.*.achievement_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // Upload documents
            $documents = [];
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $documents[] = $file->store('employees/documents', 'public');
                }
            }
            $validated['documents'] = $documents;

            // Create employee
            $employee = Employee::create($validated);

            // Create achievements
            if ($request->has('achievements')) {
                foreach ($request->achievements as $ach) {
                    $employee->achievements()->create([
                        'title' => $ach['title'],
                        'achievement_date' => $ach['achievement_date'] ?? now(),
                    ]);
                }
            }

            DB::commit();

            $employee->load(['documents', 'achievements']);

            return response()->json([
                'message' => 'Employee created successfully',
                'data' => $employee
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create employee', 'error' => $e->getMessage()], 500);
        }
    }

    // Show employee
    public function show(Employee $employee): JsonResponse
    {
        $employee->load(['documents', 'achievements', 'branch', 'department']);
        return response()->json($employee);
    }

    // Update employee
    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => "sometimes|email|unique:employees,email,{$employee->id}",
            'phone' => "nullable|string|max:20|unique:employees,phone,{$employee->id}",
            'position' => 'sometimes|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,terminated,on_leave',
            'employment_type' => 'nullable|in:full_time,part_time,contract,intern,temporary',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'remove_documents' => 'nullable|array',
            'remove_documents.*' => 'string',
            'achievements' => 'nullable|array',
            'achievements.*.title' => 'required|string|max:255',
            'achievements.*.achievement_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // Remove old documents
            $documents = $employee->documents ?? [];
            if ($request->filled('remove_documents')) {
                foreach ($request->remove_documents as $file) {
                    Storage::disk('public')->delete($file);
                    $documents = array_filter($documents, fn($doc) => $doc !== $file);
                }
            }

            // Upload new documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $documents[] = $file->store('employees/documents', 'public');
                }
            }
            $validated['documents'] = array_values($documents);

            // Update employee
            $employee->update($validated);

            // Add new achievements
            if ($request->has('achievements')) {
                foreach ($request->achievements as $ach) {
                    $employee->achievements()->create([
                        'title' => $ach['title'],
                        'achievement_date' => $ach['achievement_date'] ?? now(),
                    ]);
                }
            }

            DB::commit();

            $employee->load(['documents', 'achievements']);

            return response()->json([
                'message' => 'Employee updated successfully',
                'data' => $employee
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update employee', 'error' => $e->getMessage()], 500);
        }
    }

    // Delete employee
    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();
        return response()->json(['message' => 'Employee deleted successfully']);
    }

    // Restore employee
    public function restore($id): JsonResponse
    {
        $employee = Employee::withTrashed()->findOrFail($id);
        $employee->restore();
        return response()->json(['message' => 'Employee restored successfully']);
    }
}
