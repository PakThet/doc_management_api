<?php
// app/Imports/EmployeesImport.php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Str;

class EmployeesImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure, WithChunkReading
{
    use Importable;

    protected $organizationId;
    protected $updateExisting;
    protected $sendNotifications;
    protected $importedCount = 0;
    protected $updatedCount = 0;
    protected $failedCount = 0;
    protected $errors = [];

    public function __construct($organizationId, $updateExisting = false, $sendNotifications = false)
    {
        $this->organizationId = $organizationId;
        $this->updateExisting = $updateExisting;
        $this->sendNotifications = $sendNotifications;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Find or create department
                $department = $this->findOrCreateDepartment($row);
                if (!$department) {
                    $this->failedCount++;
                    $this->errors[] = "Row {$row['employee_code']}: Department not found or created";
                    continue;
                }

                // Find or create branch
                $branch = $this->findOrCreateBranch($row);
                if (!$branch) {
                    $this->failedCount++;
                    $this->errors[] = "Row {$row['employee_code']}: Branch not found or created";
                    continue;
                }

                // Check if employee exists
                $employee = Employee::where('organization_id', $this->organizationId)
                    ->where(function ($query) use ($row) {
                        $query->where('employee_code', $row['employee_code'])
                              ->orWhere('email', $row['email']);
                    })
                    ->first();

                $data = [
                    'organization_id' => $this->organizationId,
                    'employee_code' => $row['employee_code'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                    'phone' => $row['phone'] ?? null,
                    'department_id' => $department->id,
                    'branch_id' => $branch->id,
                    'position' => $row['position'],
                    'employment_type' => $row['employment_type'] ?? 'full_time',
                    'join_date' => $row['join_date'],
                    'salary' => $row['salary'] ?? null,
                    'status' => $row['status'] ?? 'active',
                ];

                if ($employee && $this->updateExisting) {
                    $employee->update($data);
                    $this->updatedCount++;
                } elseif (!$employee) {
                    Employee::create($data);
                    $this->importedCount++;
                    
                    // Create user account if needed
                    if ($this->sendNotifications) {
                        $this->createUserAccount($row);
                    }
                } else {
                    $this->failedCount++;
                    $this->errors[] = "Row {$row['employee_code']}: Employee already exists and update_existing is false";
                }

            } catch (\Exception $e) {
                $this->failedCount++;
                $this->errors[] = "Row {$row['employee_code']}: " . $e->getMessage();
            }
        }
    }

    /**
     * Find or create department
     */
    protected function findOrCreateDepartment($row)
    {
        if (!isset($row['department_code'])) {
            return null;
        }

        $department = Department::where('organization_id', $this->organizationId)
            ->where(function ($query) use ($row) {
                $query->where('code', $row['department_code'])
                      ->orWhere('name', $row['department_code']);
            })
            ->first();

        if (!$department) {
            $department = Department::create([
                'organization_id' => $this->organizationId,
                'name' => $row['department_name'] ?? $row['department_code'],
                'code' => $row['department_code'],
                'status' => 'active',
            ]);
        }

        return $department;
    }

    /**
     * Find or create branch
     */
    protected function findOrCreateBranch($row)
    {
        if (!isset($row['branch_code'])) {
            return null;
        }

        $branch = Branch::where('organization_id', $this->organizationId)
            ->where(function ($query) use ($row) {
                $query->where('code', $row['branch_code'])
                      ->orWhere('name', $row['branch_code']);
            })
            ->first();

        if (!$branch) {
            $branch = Branch::create([
                'organization_id' => $this->organizationId,
                'name' => $row['branch_name'] ?? $row['branch_code'],
                'code' => $row['branch_code'],
                'status' => 'active',
            ]);
        }

        return $branch;
    }

    /**
     * Create user account for employee
     */
    protected function createUserAccount($row)
    {
        $password = Str::random(10);
        
        User::create([
            'organization_id' => $this->organizationId,
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email'],
            'phone' => $row['phone'] ?? null,
            'password' => Hash::make($password),
            'status' => 'active',
        ]);

        // TODO: Send email with password
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'employee_code' => 'required|string|max:50',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'department_code' => 'required|string|max:50',
            'branch_code' => 'required|string|max:50',
            'position' => 'required|string|max:255',
            'employment_type' => 'nullable|in:full_time,part_time,contract,intern,temporary',
            'join_date' => 'required|date',
            'salary' => 'nullable|numeric',
            'status' => 'nullable|in:active,inactive,terminated,on_leave',
        ];
    }

    /**
     * Get the row count
     */
    public function getRowCount(): int
    {
        return $this->importedCount + $this->updatedCount + $this->failedCount;
    }

    /**
     * Get imported count
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * Get updated count
     */
    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    /**
     * Get failed count
     */
    public function getFailedCount(): int
    {
        return $this->failedCount;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param \Throwable $e
     */
    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }

    /**
     * @param Failure ...$failures
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    /**
     * Chunk size
     */
    public function chunkSize(): int
    {
        return 100;
    }
}