<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super Admin (no branch) ────────────────────────────────────────────
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@acme.com'],
            [
                'first_name'          => 'Super',
                'last_name'           => 'Admin',
                'phone'               => '+10000000001',
                'status'              => 'active',
                'password'            => Hash::make('Password@123'),
                'password_changed_at' => now(),
                'email_verified_at'   => now(),
            ]
        );
        $superAdmin->syncRoles(['super-admin']);

        // ── Per-branch admins, managers, staff, viewers ────────────────────────
        $branches = Branch::all();

        $index = 2;
        foreach ($branches as $branch) {
            $branchSlug = strtolower(str_replace([' ', '-'], '', $branch->code));

            // Admin
            $admin = User::firstOrCreate(
                ['email' => "admin.{$branchSlug}@acme.com"],
                [
                    'branch_id'           => $branch->id,
                    'first_name'          => 'Admin',
                    'last_name'           => $branch->name,
                    'phone'               => '+1000000' . str_pad($index++, 4, '0', STR_PAD_LEFT),
                    'status'              => 'active',
                    'password'            => Hash::make('Password@123'),
                    'password_changed_at' => now(),
                    'email_verified_at'   => now(),
                ]
            );
            $admin->syncRoles(['admin']);

            // Manager
            $manager = User::firstOrCreate(
                ['email' => "manager.{$branchSlug}@acme.com"],
                [
                    'branch_id'           => $branch->id,
                    'first_name'          => 'Manager',
                    'last_name'           => $branch->name,
                    'phone'               => '+1000000' . str_pad($index++, 4, '0', STR_PAD_LEFT),
                    'status'              => 'active',
                    'password'            => Hash::make('Password@123'),
                    'password_changed_at' => now(),
                    'email_verified_at'   => now(),
                ]
            );
            $manager->syncRoles(['manager']);

            // Staff
            $staff = User::firstOrCreate(
                ['email' => "staff.{$branchSlug}@acme.com"],
                [
                    'branch_id'           => $branch->id,
                    'first_name'          => 'Staff',
                    'last_name'           => $branch->name,
                    'phone'               => '+1000000' . str_pad($index++, 4, '0', STR_PAD_LEFT),
                    'status'              => 'active',
                    'password'            => Hash::make('Password@123'),
                    'password_changed_at' => now(),
                    'email_verified_at'   => now(),
                ]
            );
            $staff->syncRoles(['staff']);

            // Viewer
            $viewer = User::firstOrCreate(
                ['email' => "viewer.{$branchSlug}@acme.com"],
                [
                    'branch_id'           => $branch->id,
                    'first_name'          => 'Viewer',
                    'last_name'           => $branch->name,
                    'phone'               => '+1000000' . str_pad($index++, 4, '0', STR_PAD_LEFT),
                    'status'              => 'active',
                    'password'            => Hash::make('Password@123'),
                    'password_changed_at' => now(),
                    'email_verified_at'   => now(),
                ]
            );
            $viewer->syncRoles(['viewer']);
        }

        $this->command->info('Users seeded.');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['super-admin', 'superadmin@acme.com', 'Password@123'],
                ['admin',       'admin.acmehq@acme.com',      'Password@123'],
                ['manager',     'manager.acmehq@acme.com',    'Password@123'],
                ['staff',       'staff.acmehq@acme.com',      'Password@123'],
                ['viewer',      'viewer.acmehq@acme.com',     'Password@123'],
            ]
        );
    }
}