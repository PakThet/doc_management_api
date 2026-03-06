<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1️⃣ GLOBAL SUPER ADMIN (ONLY ONCE)
        |--------------------------------------------------------------------------
        */

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@system.com'],
            [
                'organization_id' => null, // 🔥 GLOBAL
                'first_name' => 'Global',
                'last_name' => 'Super Admin',
                'phone' => '999999999',
                'status' => 'active',
                'password' => Hash::make('password'),
            ]
        );

        $superAdmin->syncRoles(['Super Admin']);

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ ORGANIZATION USERS
        |--------------------------------------------------------------------------
        */

        $organizations = Organization::all();

        foreach ($organizations as $organization) {

            // Admin
            $admin = User::firstOrCreate(
                ['email' => 'admin_'.$organization->id.'@example.com'],
                [
                    'organization_id' => $organization->id,
                    'first_name' => 'Admin',
                    'last_name' => 'User',
                    'phone' => '100000000'.$organization->id,
                    'status' => 'active',
                    'password' => Hash::make('password'),
                ]
            );

            $admin->syncRoles(['Admin']);

            // Manager
            $manager = User::firstOrCreate(
                ['email' => 'manager_'.$organization->id.'@example.com'],
                [
                    'organization_id' => $organization->id,
                    'first_name' => 'Manager',
                    'last_name' => 'User',
                    'phone' => '200000000'.$organization->id,
                    'status' => 'active',
                    'password' => Hash::make('password'),
                ]
            );

            $manager->syncRoles(['Manager']);

            // Staff
            $staff = User::firstOrCreate(
                ['email' => 'staff_'.$organization->id.'@example.com'],
                [
                    'organization_id' => $organization->id,
                    'first_name' => 'Staff',
                    'last_name' => 'User',
                    'phone' => '300000000'.$organization->id,
                    'status' => 'active',
                    'password' => Hash::make('password'),
                ]
            );

            $staff->syncRoles(['Staff']);
        }
    }
}