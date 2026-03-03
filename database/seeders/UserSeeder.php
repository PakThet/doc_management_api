<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $organizations = Organization::all();

        foreach ($organizations as $org) {

            $users = $this->getUsersForOrganization($org->name);

            foreach ($users as $userData) {

                $user = User::firstOrCreate(
                    ['email' => $userData['email']],
                    [
                        'organization_id' => $org->id,
                        'first_name' => $userData['first_name'],
                        'last_name' => $userData['last_name'],
                        'phone' => $userData['phone'],
                        'status' => $userData['status'],
                        'email_verified_at' => now(),
                        'password' => Hash::make('password123'),
                    ]
                );

                // Assign roles
                if (!empty($userData['roles'])) {
                    foreach ($userData['roles'] as $roleName) {
                        $role = Role::where('name', $roleName)->where('guard_name', 'api')->first();
                        if ($role && !$user->hasRole($role)) {
                            $user->assignRole($role);
                        }
                    }
                }
            }
        }

        // Create Super Admin
        $firstOrg = Organization::first();
        if ($firstOrg) {
            $superAdmin = User::firstOrCreate(
                ['email' => 'super.admin@system.com'],
                [
                    'organization_id' => $firstOrg->id,
                    'first_name' => 'Super',
                    'last_name' => 'Admin',
                    'phone' => '+1-555-000-0001',
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password123'),
                ]
            );

            $superRole = Role::where('name', 'super-admin')->where('guard_name', 'api')->first();
            if ($superRole && !$superAdmin->hasRole($superRole)) {
                $superAdmin->assignRole($superRole);
            }
        }

        $this->command->info('✅ Users seeded successfully.');
    }

    private function getUsersForOrganization(string $orgName): array
    {
        $users = [
            'TechCorp International' => [
                ['first_name' => 'John', 'last_name' => 'Smith', 'email' => 'john.smith@techcorp.com', 'phone' => '+1-555-123-1001', 'status' => 'active', 'roles' => ['admin']],
                ['first_name' => 'Sarah', 'last_name' => 'Johnson', 'email' => 'sarah.johnson@techcorp.com', 'phone' => '+1-555-123-1002', 'status' => 'active', 'roles' => ['admin']],
                ['first_name' => 'Michael', 'last_name' => 'Chen', 'email' => 'michael.chen@techcorp.com', 'phone' => '+1-555-123-1003', 'status' => 'active', 'roles' => ['manager']],
                ['first_name' => 'Emily', 'last_name' => 'Davis', 'email' => 'emily.davis@techcorp.com', 'phone' => '+1-555-123-1004', 'status' => 'active', 'roles' => ['hr-manager']],
                ['first_name' => 'Maria', 'last_name' => 'Garcia', 'email' => 'maria.garcia@techcorp.com', 'phone' => '+1-555-123-1008', 'status' => 'active', 'roles' => ['document-controller']],
                ['first_name' => 'Robert', 'last_name' => 'Martinez', 'email' => 'robert.martinez@techcorp.com', 'phone' => '+1-555-123-1009', 'status' => 'active', 'roles' => ['employee']],
            ],
            'Global Solutions Ltd' => [
                ['first_name' => 'William', 'last_name' => 'Thompson', 'email' => 'william.thompson@globalsolutions.com', 'phone' => '+44-20-1234-1001', 'status' => 'active', 'roles' => ['admin']],
                ['first_name' => 'Elizabeth', 'last_name' => 'Roberts', 'email' => 'elizabeth.roberts@globalsolutions.com', 'phone' => '+44-20-1234-1002', 'status' => 'active', 'roles' => ['manager']],
            ],
            'InnovateTech Asia' => [
                ['first_name' => 'Kevin', 'last_name' => 'Tan', 'email' => 'kevin.tan@innovatetech.asia', 'phone' => '+65-6789-1001', 'status' => 'active', 'roles' => ['admin']],
            ],
            'MediCare Health Systems' => [
                ['first_name' => 'Richard', 'last_name' => 'Miller', 'email' => 'richard.miller@medicarehealth.com', 'phone' => '+1-555-987-1001', 'status' => 'active', 'roles' => ['admin']],
            ],
            'EduWorld Learning' => [
                ['first_name' => 'Christopher', 'last_name' => 'Lee', 'email' => 'christopher.lee@eduworld.com', 'phone' => '+61-2-9876-1001', 'status' => 'active', 'roles' => ['admin']],
            ],
        ];

        return $users[$orgName] ?? [];
    }
}