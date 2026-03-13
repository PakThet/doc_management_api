<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters — each seeder depends on data from the previous ones.
     *
     *  1. RolesAndPermissionsSeeder  — Spatie roles/permissions (no dependencies)
     *  2. BranchSeeder               — Branches        (no dependencies)
     *  3. DepartmentSeeder           — Departments     (needs branches)
     *  4. PositionSeeder             — Positions       (needs departments)
     *  5. EmployeeSeeder             — Employees       (needs branches + departments + positions)
     *  6. DepartmentHeadSeeder       — Assigns HOD     (needs departments + employees)
     *  7. UserSeeder                 — System users    (needs branches + roles)
     *  8. DocumentCategorySeeder     — Doc categories  (no dependencies)
     *  9. DocumentPrefixSeeder       — Doc prefixes    (needs branches)
     * 10. DocumentGroupSeeder        — Doc groups      (needs branches + users)
     * 11. DocumentSeeder             — Documents       (needs all of the above)
     * 12. EmployeeDocumentSeeder     — Employee docs   (needs employees)
     * 13. AchievementSeeder          — Achievements    (needs employees)
     * 14. ActivityLogSeeder          — Activity logs   (needs all of the above)
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            BranchSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            PositionSeeder::class,
            EmployeeSeeder::class,
            DepartmentHeadSeeder::class,
            DocumentCategorySeeder::class,
            DocumentPrefixSeeder::class,
            DocumentGroupSeeder::class,
            DocumentSeeder::class,
            EmployeeDocumentSeeder::class,
            AchievementSeeder::class,
            ActivityLogSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅  All seeders completed successfully.');
        $this->command->info('');
        $this->command->info('Default login credentials:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['super-admin', 'superadmin@acme.com',       'Password@123'],
                ['admin',       'admin.acmehq@acme.com',     'Password@123'],
                ['manager',     'manager.acmehq@acme.com',   'Password@123'],
                ['staff',       'staff.acmehq@acme.com',     'Password@123'],
                ['viewer',      'viewer.acmehq@acme.com',    'Password@123'],
            ]
        );
    }
}