<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            OrganizationSeeder::class,
            BranchSeeder::class,
            DepartmentSeeder::class,
            EmployeeSeeder::class,
            UserSeeder::class,
            DocumentCategorySeeder::class,
            DocumentPrefixSeeder::class,
            DocumentSeeder::class,
            ActivityLogSeeder::class,
        ]);
    }
}
