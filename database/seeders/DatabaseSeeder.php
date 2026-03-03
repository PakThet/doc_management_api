<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Then create organizations
            OrganizationSeeder::class,
            
            // First, create permissions and roles
            PermissionRoleSeeder::class,
            
            
            // Create branches (depends on organizations)
            BranchSeeder::class,
            
            // Create departments (depends on organizations)
            DepartmentSeeder::class,
            
            // Create users (depends on organizations)
            UserSeeder::class,
            
            // Create employees (depends on users, branches, departments)
            EmployeeSeeder::class,
            
            // Create document categories and prefixes (depends on organizations)
            DocumentCategorySeeder::class,
            DocumentPrefixSeeder::class,
            
            
            // Optional: Create activity logs
            ActivityLogSeeder::class,
            // Create documents (depends on almost everything)
            DocumentSeeder::class,
        ]);
        
        $this->command->info('All database seeders completed successfully!');
    }
}