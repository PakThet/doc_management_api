<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $branch = \App\Models\Branch::first();
        if (! $branch) {
            return;
        }

        $super = User::updateOrCreate(
            ['email' => 'superadmin@main.com'],
            [
                'branch_id' => $branch->id,
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'phone' => '+85590000001',
            ]
        );

        $super->syncRoles(['super_admin']);

        $admin = User::updateOrCreate(
            ['email' => 'admin@main.com'],
            [
                'branch_id' => $branch->id,
                'first_name' => 'Branch',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'phone' => '+85590000002',
            ]
        );

        $admin->syncRoles(['admin']);

        User::updateOrCreate(
            ['email' => 'manager@main.com'],
            [
                'branch_id' => $branch->id,
                'first_name' => 'Department',
                'last_name' => 'Manager',
                'password' => Hash::make('password'),
                'status' => 'active',
                'phone' => '+85590000003',
            ]
        )->syncRoles(['manager']);
    }
}
