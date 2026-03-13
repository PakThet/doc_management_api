<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all()->keyBy('code');

        foreach ($branches as $code => $branch) {
            $this->seedBranchEmployees($branch, $code);
        }

        $this->command->info('Employees seeded.');
    }

    private function seedBranchEmployees(Branch $branch, string $branchCode): void
    {
        $depts = Department::where('branch_id', $branch->id)
            ->whereNull('parent_id')
            ->get()
            ->keyBy('code');

        // Helper to find dept
        $dept = fn(string $suffix) => $depts["{$branchCode}-{$suffix}"] ?? null;

        $employees = [
            // ── Executive ──────────────────────────────────────────────────────
            [
                'first_name'      => 'Alexandra',
                'last_name'       => 'Chen',
                'position_title'  => 'Chief Executive Officer',
                'employment_type' => 'full_time',
                'department_id'   => $dept('EXEC')?->id,
                'salary'          => 250000.00,
                'join_date'       => '2000-01-15',
                'status'          => 'active',
            ],
            [
                'first_name'      => 'Marcus',
                'last_name'       => 'Rivera',
                'position_title'  => 'Chief Operating Officer',
                'employment_type' => 'full_time',
                'department_id'   => $dept('EXEC')?->id,
                'salary'          => 200000.00,
                'join_date'       => '2002-03-01',
                'status'          => 'active',
            ],
            // ── HR ─────────────────────────────────────────────────────────────
            [
                'first_name'      => 'Sophia',
                'last_name'       => 'Patel',
                'position_title'  => 'HR Manager',
                'employment_type' => 'full_time',
                'department_id'   => $dept('HR')?->id,
                'salary'          => 85000.00,
                'join_date'       => '2015-07-01',
                'status'          => 'active',
            ],
            [
                'first_name'      => 'James',
                'last_name'       => 'Nguyen',
                'position_title'  => 'HR Specialist',
                'employment_type' => 'full_time',
                'department_id'   => $dept('HR')?->id,
                'salary'          => 60000.00,
                'join_date'       => '2019-04-15',
                'status'          => 'active',
            ],
            // ── Finance ────────────────────────────────────────────────────────
            [
                'first_name'      => 'Diana',
                'last_name'       => 'Müller',
                'position_title'  => 'Chief Financial Officer',
                'employment_type' => 'full_time',
                'department_id'   => $dept('FIN')?->id,
                'salary'          => 180000.00,
                'join_date'       => '2008-11-01',
                'status'          => 'active',
            ],
            [
                'first_name'      => 'Kevin',
                'last_name'       => 'O\'Brien',
                'position_title'  => 'Accountant',
                'employment_type' => 'full_time',
                'department_id'   => $dept('FIN')?->id,
                'salary'          => 75000.00,
                'join_date'       => '2017-02-20',
                'status'          => 'active',
            ],
            // ── IT ─────────────────────────────────────────────────────────────
            [
                'first_name'      => 'Nathan',
                'last_name'       => 'Brooks',
                'position_title'  => 'Chief Technology Officer',
                'employment_type' => 'full_time',
                'department_id'   => $dept('IT')?->id,
                'salary'          => 190000.00,
                'join_date'       => '2010-05-10',
                'status'          => 'active',
            ],
            [
                'first_name'      => 'Priya',
                'last_name'       => 'Sharma',
                'position_title'  => 'Software Engineer',
                'employment_type' => 'full_time',
                'department_id'   => $dept('IT')?->id,
                'salary'          => 120000.00,
                'join_date'       => '2016-08-01',
                'status'          => 'active',
            ],
            [
                'first_name'      => 'Carlos',
                'last_name'       => 'Mendez',
                'position_title'  => 'DevOps Engineer',
                'employment_type' => 'full_time',
                'department_id'   => $dept('IT')?->id,
                'salary'          => 110000.00,
                'join_date'       => '2018-03-15',
                'status'          => 'active',
            ],
            [
                'first_name'      => 'Aisha',
                'last_name'       => 'Johnson',
                'position_title'  => 'Software Engineer',
                'employment_type' => 'full_time',
                'department_id'   => $dept('IT')?->id,
                'salary'          => 95000.00,
                'join_date'       => '2021-01-10',
                'status'          => 'active',
            ],
            // ── Operations ─────────────────────────────────────────────────────
            [
                'first_name'      => 'Robert',
                'last_name'       => 'Kim',
                'position_title'  => 'Operations Manager',
                'employment_type' => 'full_time',
                'department_id'   => $dept('OPS')?->id,
                'salary'          => 90000.00,
                'join_date'       => '2013-09-01',
                'status'          => 'active',
            ],
            [
                'first_name'      => 'Fatima',
                'last_name'       => 'Al-Hassan',
                'position_title'  => 'Business Analyst',
                'employment_type' => 'full_time',
                'department_id'   => $dept('OPS')?->id,
                'salary'          => 65000.00,
                'join_date'       => '2020-06-01',
                'status'          => 'active',
            ],
            [
                'first_name'      => 'Liam',
                'last_name'       => 'Thompson',
                'position_title'  => 'Project Manager',
                'employment_type' => 'contract',
                'department_id'   => $dept('OPS')?->id,
                'salary'          => 50000.00,
                'join_date'       => '2022-03-01',
                'status'          => 'active',
            ],
        ];

        $counter = Employee::where('branch_id', $branch->id)->count();

        foreach ($employees as $index => $data) {
            $counter++;
            $paddedCode = str_pad($counter, 4, '0', STR_PAD_LEFT);
            $employeeCode = "{$branchCode}-EMP-{$paddedCode}";
            $slug = strtolower($branchCode);

            $position = Position::where('position_title', $data['position_title'])
                ->where('department_id', $data['department_id'])
                ->first();

            unset($data['position_title']);

            Employee::firstOrCreate(
                ['employee_code' => $employeeCode],
                array_merge($data, [
                    'branch_id'     => $branch->id,
                    'position_id'   => $position?->id,
                    'employee_code' => $employeeCode,
                    'email'         => strtolower("{$data['first_name']}.{$data['last_name']}.{$counter}@{$slug}.internal"),
                    'phone'         => '+1' . rand(2000000000, 9999999999),
                    'address'       => $branch->address,
                    'date_of_birth' => now()->subYears(rand(28, 55))->subDays(rand(0, 365))->toDateString(),
                    'join_date'     => $data['join_date'],
                    'bank_details'  => [
                        'bank_name'      => 'National Bank',
                        'account_number' => '****' . rand(1000, 9999),
                        'routing_number' => '****' . rand(1000, 9999),
                    ],
                    'metadata' => ['source' => 'seeder'],
                ])
            );
        }
    }
}