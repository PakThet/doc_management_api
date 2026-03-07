<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $branches = \App\Models\Branch::query()->with('departments')->get();
        $counter = 1;

        foreach ($branches as $branch) {
            $department = $branch->departments->first();

            if (! $department) {
                continue;
            }

            for ($i = 1; $i <= 5; $i++) {
                $employeeCode = 'EMP-' . str_pad((string) $branch->id, 2, '0', STR_PAD_LEFT) . '-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $email = 'employee' . $counter . '@example.local';

                Employee::updateOrCreate(
                    ['employee_code' => $employeeCode],
                    [
                        'branch_id' => $branch->id,
                        'department_id' => $department->id,
                        'first_name' => 'Employee',
                        'last_name' => (string) $counter,
                        'email' => $email,
                        'phone' => '+8553000' . str_pad((string) $counter, 3, '0', STR_PAD_LEFT),
                        'join_date' => now()->subDays($counter),
                        'position' => 'Staff',
                        'status' => 'active',
                        'employment_type' => 'full_time',
                    ]
                );

                $counter++;
            }
        }

        $firstDepartment = \App\Models\Department::query()->first();
        $firstBranch = \App\Models\Branch::query()->first();

        if ($firstDepartment && $firstBranch) {
            $manager = Employee::query()->where('department_id', $firstDepartment->id)->first();

            if ($manager) {
                $firstDepartment->update(['head_of_department_id' => $manager->id]);
            }
        }
    }
}
