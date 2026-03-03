<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Organization;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        |---------------------------------------
        | 1. Create Global Permissions
        |---------------------------------------
        */

        $permissions = [
            'view users','create users','edit users','delete users',
            'view organizations','create organizations','edit organizations','delete organizations',
            'view branches','create branches','edit branches','delete branches',
            'view departments','create departments','edit departments','delete departments',
            'view employees','create employees','edit employees','delete employees',
            'view documents','create documents','edit documents','delete documents','verify documents',
            'view document-categories','create document-categories','edit document-categories','delete document-categories',
            'view document-prefixes','create document-prefixes','edit document-prefixes','delete document-prefixes',
            'view roles','create roles','edit roles','delete roles',
            'view permissions','assign permissions',
            'view dashboard','view statistics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }

        /*
        |---------------------------------------
        | 2. Create Roles Per Organization
        |---------------------------------------
        */

        $organizations = Organization::all();

        foreach ($organizations as $organization) {

            app(PermissionRegistrar::class)
                ->setPermissionsTeamId($organization->id);

            $roleNames = [
                'super-admin',
                'admin',
                'manager',
                'hr-manager',
                'document-controller',
                'employee'
            ];

            $roles = [];

            foreach ($roleNames as $name) {
                $roles[$name] = Role::firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'api',
                ]);
            }

            // Assign Permissions

            $roles['super-admin']->syncPermissions(Permission::all());

            $roles['admin']->syncPermissions([
                'view users','create users','edit users',
                'view branches','view departments','view employees',
                'view documents','verify documents',
                'view dashboard','view statistics'
            ]);

            $roles['manager']->syncPermissions([
                'view employees','create employees','edit employees',
                'view documents','view dashboard'
            ]);

            $roles['hr-manager']->syncPermissions([
                'view employees','create employees','edit employees','delete employees',
                'view departments','view dashboard','view statistics'
            ]);

            $roles['document-controller']->syncPermissions([
                'view documents','create documents','edit documents','delete documents','verify documents',
                'view document-categories','view document-prefixes',
                'view dashboard'
            ]);

            $roles['employee']->syncPermissions([
                'view documents'
            ]);
        }

        $this->command->info('✅ Roles and Permissions seeded successfully.');
    }
}