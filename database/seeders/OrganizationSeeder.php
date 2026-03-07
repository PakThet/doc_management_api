<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        Organization::updateOrCreate(
            ['slug' => 'main-organization'],
            [
                'name' => 'Main Organization',
                'email' => 'info@main.com',
                'phone' => '+85510000001',
                'address' => 'Phnom Penh, Cambodia',
                'website' => 'https://main.local',
                'status' => 'active',
                'settings' => [
                    'timezone' => 'Asia/Bangkok',
                    'currency' => 'USD',
                ],
            ]
        );

        Organization::updateOrCreate(
            ['slug' => 'support-organization'],
            [
                'name' => 'Support Organization',
                'email' => 'support@main.com',
                'phone' => '+85510000002',
                'address' => 'Siem Reap, Cambodia',
                'website' => 'https://support.local',
                'status' => 'active',
                'settings' => [
                    'timezone' => 'Asia/Bangkok',
                    'currency' => 'USD',
                ],
            ]
        );
    }
}
