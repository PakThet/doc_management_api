<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

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

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ GLOBAL SUPER ADMIN (NO ORGANIZATION)
        |--------------------------------------------------------------------------
        */

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
                'organization_id' => null,
            ]);
        }

        $globalSuperAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'api',
            'organization_id' => null,
        ]);

        $globalSuperAdmin->syncPermissions(
            Permission::whereNull('organization_id')->get()
        );

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ ORGANIZATION ROLES
        |--------------------------------------------------------------------------
        */

        $organizations = Organization::all();

        foreach ($organizations as $organization) {

            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'api',
                    'organization_id' => $organization->id,
                ]);
            }

            $orgPermissions = Permission::where('organization_id', $organization->id)->get();

            $admin = Role::firstOrCreate([
                'name' => 'Admin',
                'guard_name' => 'api',
                'organization_id' => $organization->id,
            ]);

            $manager = Role::firstOrCreate([
                'name' => 'Manager',
                'guard_name' => 'api',
                'organization_id' => $organization->id,
            ]);

            $staff = Role::firstOrCreate([
                'name' => 'Staff',
                'guard_name' => 'api',
                'organization_id' => $organization->id,
            ]);

            $admin->syncPermissions($orgPermissions);

            $manager->syncPermissions(
                $orgPermissions->whereNotIn('name', [
                    'create roles','edit roles','delete roles',
                    'assign permissions','delete organizations'
                ])
            );

            $staff->syncPermissions(
                $orgPermissions->filter(fn ($p) =>
                    str_starts_with($p->name, 'view')
                )
            );
        }
    }
}