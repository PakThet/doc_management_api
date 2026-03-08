<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\DocumentPrefix;
use Illuminate\Database\Seeder;

class DocumentPrefixSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all()->keyBy('code');

        foreach ($branches as $branchCode => $branch) {
            $this->seedForBranch($branch, $branchCode);
        }

        $this->command->info('Document prefixes seeded.');
    }

    private function seedForBranch(Branch $branch, string $branchCode): void
    {
        // Map dept code suffix → prefix definitions
        $prefixMap = [
            'HR' => [
                [
                    'name'        => 'Employee Contracts',
                    'prefix'      => "{$branchCode}-HR-CON",
                    'separator'   => '-',
                    'format'      => '{PREFIX}{SEP}{YEAR}{SEP}{SEQ:5}',
                    'description' => 'Employment contract documents',
                    'is_default'  => true,
                ],
                [
                    'name'        => 'HR Policies',
                    'prefix'      => "{$branchCode}-HR-POL",
                    'separator'   => '-',
                    'format'      => '{PREFIX}{SEP}{YEAR}{SEP}{SEQ:5}',
                    'description' => 'Human resources policy documents',
                    'is_default'  => false,
                ],
            ],
            'FIN' => [
                [
                    'name'        => 'Financial Reports',
                    'prefix'      => "{$branchCode}-FIN-RPT",
                    'separator'   => '-',
                    'format'      => '{PREFIX}{SEP}{YEAR}{SEP}{MONTH}{SEP}{SEQ:4}',
                    'description' => 'Monthly and quarterly financial reports',
                    'is_default'  => true,
                ],
                [
                    'name'        => 'Invoices',
                    'prefix'      => "{$branchCode}-FIN-INV",
                    'separator'   => '-',
                    'format'      => '{PREFIX}{SEP}{YEAR}{SEP}{SEQ:6}',
                    'description' => 'Invoice documents',
                    'is_default'  => false,
                ],
            ],
            'IT' => [
                [
                    'name'        => 'Technical Specs',
                    'prefix'      => "{$branchCode}-IT-SPEC",
                    'separator'   => '-',
                    'format'      => '{PREFIX}{SEP}{YEAR}{SEP}{SEQ:5}',
                    'description' => 'Technical specification documents',
                    'is_default'  => true,
                ],
                [
                    'name'        => 'IT Policies',
                    'prefix'      => "{$branchCode}-IT-POL",
                    'separator'   => '-',
                    'format'      => '{PREFIX}{SEP}{YEAR}{SEP}{SEQ:4}',
                    'description' => 'IT security and usage policies',
                    'is_default'  => false,
                ],
            ],
            'OPS' => [
                [
                    'name'        => 'Operations Reports',
                    'prefix'      => "{$branchCode}-OPS-RPT",
                    'separator'   => '-',
                    'format'      => '{PREFIX}{SEP}{YEAR}{SEP}{SEQ:5}',
                    'description' => 'Operational reports and summaries',
                    'is_default'  => true,
                ],
            ],
            'EXEC' => [
                [
                    'name'        => 'Executive Memos',
                    'prefix'      => "{$branchCode}-EXEC-MEM",
                    'separator'   => '-',
                    'format'      => '{PREFIX}{SEP}{YEAR}{SEP}{SEQ:4}',
                    'description' => 'Executive-level memos and announcements',
                    'is_default'  => true,
                ],
            ],
        ];

        foreach ($prefixMap as $deptSuffix => $prefixes) {
            $dept = Department::where('branch_id', $branch->id)
                ->where('code', "{$branchCode}-{$deptSuffix}")
                ->first();

            if (! $dept) {
                continue;
            }

            foreach ($prefixes as $prefixData) {
                DocumentPrefix::firstOrCreate(
                    ['prefix' => $prefixData['prefix']],
                    array_merge($prefixData, [
                        'department_id' => $dept->id,
                        'status'        => 'active',
                        'metadata'      => ['branch_code' => $branchCode],
                    ])
                );
            }
        }
    }
}