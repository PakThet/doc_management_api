<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'branches.view', 'branches.create', 'branches.update', 'branches.delete',
            'departments.view', 'departments.create', 'departments.update', 'departments.delete',
            'employees.view', 'employees.create', 'employees.update', 'employees.delete',
            'documents.view', 'documents.create', 'documents.update', 'documents.delete',
            'roles.manage', 'permissions.manage',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'api',
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'api',
        ]);

        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api',
        ]);

        $manager = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'api',
        ]);

        $superAdmin->syncPermissions(Permission::query()->pluck('name'));

        Role::query()->where('name', 'admin')->first()?->syncPermissions([
            'users.view', 'users.create', 'users.update',
            'branches.view', 'departments.view', 'departments.create', 'departments.update',
            'employees.view', 'employees.create', 'employees.update',
            'documents.view', 'documents.create', 'documents.update',
        ]);

        $manager->syncPermissions([
            'departments.view',
            'employees.view', 'employees.update',
            'documents.view',
        ]);
    }
}
