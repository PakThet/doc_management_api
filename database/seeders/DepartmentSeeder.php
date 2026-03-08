<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $hq = Branch::where('code', 'ACME-HQ')->first();
        $wc = Branch::where('code', 'ACME-WC')->first();
        $ch = Branch::where('code', 'ACME-CHI')->first();
        $lo = Branch::where('code', 'GT-LON')->first();
        $sg = Branch::where('code', 'GT-SGP')->first();

        // ── ACME HQ departments ────────────────────────────────────────────────
        $this->seedBranchDepartments($hq, 'ACME-HQ');

        // ── ACME West Coast ────────────────────────────────────────────────────
        $this->seedBranchDepartments($wc, 'ACME-WC');

        // ── ACME Chicago ───────────────────────────────────────────────────────
        $this->seedBranchDepartments($ch, 'ACME-CHI');

        // ── GlobalTech London ──────────────────────────────────────────────────
        $this->seedBranchDepartments($lo, 'GT-LON');

        // ── GlobalTech Singapore ───────────────────────────────────────────────
        $this->seedBranchDepartments($sg, 'GT-SGP');

        $this->command->info('Departments seeded.');
    }

    private function seedBranchDepartments(Branch $branch, string $prefix): void
    {
        $topLevel = [
            [
                'branch_id'   => $branch->id,
                'name'        => 'Executive',
                'code'        => "{$prefix}-EXEC",
                'description' => 'Executive leadership',
                'email'       => 'exec@' . strtolower(str_replace('-', '', $prefix)) . '.internal',
                'status'      => 'active',
                'budget'      => 500000.00,
            ],
            [
                'branch_id'   => $branch->id,
                'name'        => 'Human Resources',
                'code'        => "{$prefix}-HR",
                'description' => 'People operations and talent management',
                'email'       => 'hr@' . strtolower(str_replace('-', '', $prefix)) . '.internal',
                'status'      => 'active',
                'budget'      => 200000.00,
            ],
            [
                'branch_id'   => $branch->id,
                'name'        => 'Finance',
                'code'        => "{$prefix}-FIN",
                'description' => 'Financial planning, accounting, and reporting',
                'email'       => 'finance@' . strtolower(str_replace('-', '', $prefix)) . '.internal',
                'status'      => 'active',
                'budget'      => 300000.00,
            ],
            [
                'branch_id'   => $branch->id,
                'name'        => 'Information Technology',
                'code'        => "{$prefix}-IT",
                'description' => 'Technology infrastructure and software development',
                'email'       => 'it@' . strtolower(str_replace('-', '', $prefix)) . '.internal',
                'status'      => 'active',
                'budget'      => 450000.00,
            ],
            [
                'branch_id'   => $branch->id,
                'name'        => 'Operations',
                'code'        => "{$prefix}-OPS",
                'description' => 'Business operations and logistics',
                'email'       => 'ops@' . strtolower(str_replace('-', '', $prefix)) . '.internal',
                'status'      => 'active',
                'budget'      => 350000.00,
            ],
        ];

        $created = [];
        foreach ($topLevel as $dept) {
            $created[$dept['code']] = Department::firstOrCreate(
                ['code' => $dept['code']],
                $dept
            );
        }

        // Sub-departments under IT
        $itParent = $created["{$prefix}-IT"];
        $subDepts = [
            [
                'branch_id'   => $branch->id,
                'name'        => 'Software Engineering',
                'code'        => "{$prefix}-IT-SWE",
                'description' => 'Application and software development',
                'parent_id'   => $itParent->id,
                'status'      => 'active',
                'budget'      => 200000.00,
            ],
            [
                'branch_id'   => $branch->id,
                'name'        => 'Infrastructure & DevOps',
                'code'        => "{$prefix}-IT-INFRA",
                'description' => 'Cloud infrastructure and CI/CD pipelines',
                'parent_id'   => $itParent->id,
                'status'      => 'active',
                'budget'      => 150000.00,
            ],
            [
                'branch_id'   => $branch->id,
                'name'        => 'IT Support',
                'code'        => "{$prefix}-IT-SUPPORT",
                'description' => 'End-user support and helpdesk',
                'parent_id'   => $itParent->id,
                'status'      => 'active',
                'budget'      => 80000.00,
            ],
        ];

        // Sub-departments under HR
        $hrParent = $created["{$prefix}-HR"];
        $subDepts = array_merge($subDepts, [
            [
                'branch_id'   => $branch->id,
                'name'        => 'Recruitment',
                'code'        => "{$prefix}-HR-REC",
                'description' => 'Talent acquisition and hiring',
                'parent_id'   => $hrParent->id,
                'status'      => 'active',
                'budget'      => 80000.00,
            ],
            [
                'branch_id'   => $branch->id,
                'name'        => 'Payroll',
                'code'        => "{$prefix}-HR-PAY",
                'description' => 'Salary processing and compensation',
                'parent_id'   => $hrParent->id,
                'status'      => 'active',
                'budget'      => 60000.00,
            ],
        ]);

        foreach ($subDepts as $sub) {
            Department::firstOrCreate(['code' => $sub['code']], $sub);
        }
    }
}