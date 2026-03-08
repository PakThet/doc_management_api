<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $resources = [
            'organizations',
            'branches',
            'departments',
            'employees',
            'users',
            'documents',
            'document-categories',
            'document-prefixes',
            'roles',
            'permissions',
            'activity-logs',
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        // Create all permissions
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$resource}"]);
            }
        }

        // ── super-admin: all permissions ──────────────────────────────────────
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->syncPermissions(Permission::all());

        // ── admin: everything except roles/permissions management ─────────────
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(
            Permission::whereNotIn('name', [
                'view roles', 'create roles', 'edit roles', 'delete roles',
                'view permissions', 'create permissions', 'edit permissions', 'delete permissions',
            ])->get()
        );

        // ── manager: branch-level management ──────────────────────────────────
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'view organizations',
            'view branches', 'edit branches',
            'view departments', 'create departments', 'edit departments', 'delete departments',
            'view employees', 'create employees', 'edit employees', 'delete employees',
            'view users', 'create users', 'edit users',
            'view documents', 'create documents', 'edit documents', 'delete documents',
            'view document-categories',
            'view document-prefixes', 'create document-prefixes', 'edit document-prefixes',
            'view activity-logs',
        ]);

        // ── staff: view + own document management ─────────────────────────────
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->syncPermissions([
            'view departments',
            'view employees',
            'view documents', 'create documents', 'edit documents',
            'view document-categories',
            'view document-prefixes',
        ]);

        // ── viewer: read-only ─────────────────────────────────────────────────
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'view departments',
            'view employees',
            'view documents',
            'view document-categories',
            'view document-prefixes',
        ]);

        $this->command->info('Roles and permissions seeded successfully.');
    }
}