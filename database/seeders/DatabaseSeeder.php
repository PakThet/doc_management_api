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
     *  2. OrganizationSeeder         — Organizations (no dependencies)
     *  3. BranchSeeder               — Branches        (needs organizations)
     *  4. DepartmentSeeder           — Departments     (needs branches)
     *  5. EmployeeSeeder             — Employees       (needs branches + departments)
     *  6. DepartmentHeadSeeder       — Assigns HOD     (needs departments + employees)
     *  7. UserSeeder                 — System users    (needs branches + roles)
     *  8. DocumentCategorySeeder     — Doc categories  (no dependencies)
     *  9. DocumentPrefixSeeder       — Doc prefixes    (needs branches + departments)
     * 10. DocumentSeeder             — Documents       (needs all of the above)
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            OrganizationSeeder::class,
            BranchSeeder::class,
            DepartmentSeeder::class,
            EmployeeSeeder::class,
            DepartmentHeadSeeder::class,
            UserSeeder::class,
            DocumentCategorySeeder::class,
            DocumentPrefixSeeder::class,
            DocumentSeeder::class,
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