<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;

/**
 * Must run AFTER EmployeeSeeder.
 * Assigns the head_of_department_id for top-level departments.
 */
class DepartmentHeadSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all()->keyBy('code');

        foreach ($branches as $code => $branch) {
            $this->assignHeads($branch, $code);
        }

        $this->command->info('Department heads assigned.');
    }

    private function assignHeads(Branch $branch, string $branchCode): void
    {
        // Map department code suffix → job title of the head
        $headMap = [
            'EXEC'   => 'Chief Executive Officer',
            'HR'     => 'HR Manager',
            'FIN'    => 'Chief Financial Officer',
            'IT'     => 'Chief Technology Officer',
            'OPS'    => 'Operations Manager',
        ];

        foreach ($headMap as $deptSuffix => $position) {
            $dept = Department::where('branch_id', $branch->id)
                ->where('code', "{$branchCode}-{$deptSuffix}")
                ->first();

            if (! $dept) {
                continue;
            }

            $head = Employee::where('branch_id', $branch->id)
                ->where('position', $position)
                ->first();

            if ($head) {
                $dept->update(['head_of_department_id' => $head->id]);
            }
        }
    }
}