<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $branches = \App\Models\Branch::query()->get();
        $departments = [
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Finance', 'code' => 'FIN'],
        ];

        foreach ($branches as $branch) {
            foreach ($departments as $department) {
                $branchCode = $branch->code ?: ('BR' . str_pad((string) $branch->id, 3, '0', STR_PAD_LEFT));
                $departmentCode = $branchCode . '-' . $department['code'];

                Department::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'name' => $department['name'],
                    ],
                    [
                        'code' => $departmentCode,
                        'description' => $department['name'] . ' department of ' . $branch->name,
                        'email' => strtolower($department['code']) . '@' . strtolower($branchCode) . '.local',
                        'phone' => '+8552000' . str_pad((string) ($branch->id + strlen($department['code'])), 3, '0', STR_PAD_LEFT),
                        'location' => $branch->city,
                        'status' => 'active',
                        'metadata' => ['seeded' => true],
                    ]
                );
            }
        }
    }
}
