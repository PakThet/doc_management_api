<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | CREATE PERMISSIONS
        |--------------------------------------------------------------------------
        */

        $permissions = [

            // Users
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Roles
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Permissions
            'view permissions',

            // Organizations
            'view organizations',
            'create organizations',
            'edit organizations',
            'delete organizations',

            // Branches
            'view branches',
            'create branches',
            'edit branches',
            'delete branches',

            // Document Categories
            'view document categories',
            'create document categories',
            'edit document categories',
            'delete document categories',

            // Documents
            'view documents',
            'create documents',
            'edit documents',
            'delete documents',
            
            // Documents
            'view document prefixes',
            'create document prefixes',
            'edit document prefixes',
            'delete document prefixes',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE ROLES
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web'
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web'
        ]);

        $manager = Role::firstOrCreate([
            'name' => 'Manager',
            'guard_name' => 'web'
        ]);

        $user = Role::firstOrCreate([
            'name' => 'User',
            'guard_name' => 'web'
        ]);

        /*
        |--------------------------------------------------------------------------
        | ASSIGN PERMISSIONS
        |--------------------------------------------------------------------------
        */

        // Super Admin = All
        $superAdmin->syncPermissions(Permission::all());

        // Admin
        $admin->syncPermissions([
            'view users','create users','edit users',
            'view branches','create branches','edit branches',
            'view document categories','create document categories',
            'view documents','create documents','edit documents','delete documents',
            'view organizations'
        ]);

        // Manager
        $manager->syncPermissions([
            'view documents',
            'create documents',
            'edit documents'
        ]);

        // User
        $user->syncPermissions([
            'view documents'
        ]);

        /*
        |--------------------------------------------------------------------------
        | CREATE SUPER ADMIN USER
        |--------------------------------------------------------------------------
        */

        $adminUser = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Safer than assignRole in seeder
        $adminUser->syncRoles(['Super Admin']);
    }
}