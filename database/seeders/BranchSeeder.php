<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $acme       = Organization::where('slug', 'acme-corporation')->first();
        $globalTech = Organization::where('slug', 'global-tech-ltd')->first();

        $branches = [
            // Acme branches
            [
                'organization_id'  => $acme->id,
                'name'             => 'Acme HQ',
                'code'             => 'ACME-HQ',
                'address'          => '1 Acme Blvd, New York, NY 10001',
                'phone'            => '+1-212-000-0001',
                'email'            => 'hq@acme.com',
                'city'             => 'New York',
                'state'            => 'NY',
                'country'          => 'USA',
                'postal_code'      => '10001',
                'established_date' => '2000-01-15',
                'status'           => 'active',
                'settings'         => ['working_hours' => '09:00-18:00'],
            ],
            [
                'organization_id'  => $acme->id,
                'name'             => 'Acme West Coast',
                'code'             => 'ACME-WC',
                'address'          => '500 Silicon Ave, San Francisco, CA 94105',
                'phone'            => '+1-415-000-0002',
                'email'            => 'westcoast@acme.com',
                'city'             => 'San Francisco',
                'state'            => 'CA',
                'country'          => 'USA',
                'postal_code'      => '94105',
                'established_date' => '2005-06-01',
                'status'           => 'active',
                'settings'         => ['working_hours' => '08:00-17:00'],
            ],
            [
                'organization_id'  => $acme->id,
                'name'             => 'Acme Chicago',
                'code'             => 'ACME-CHI',
                'address'          => '200 Michigan Ave, Chicago, IL 60601',
                'phone'            => '+1-312-000-0003',
                'email'            => 'chicago@acme.com',
                'city'             => 'Chicago',
                'state'            => 'IL',
                'country'          => 'USA',
                'postal_code'      => '60601',
                'established_date' => '2010-03-10',
                'status'           => 'active',
                'settings'         => ['working_hours' => '09:00-18:00'],
            ],
            // GlobalTech branches
            [
                'organization_id'  => $globalTech->id,
                'name'             => 'GlobalTech London HQ',
                'code'             => 'GT-LON',
                'address'          => '10 Tech Square, London, EC1A 1BB',
                'phone'            => '+44-20-0000-0001',
                'email'            => 'london@globaltech.io',
                'city'             => 'London',
                'state'            => 'England',
                'country'          => 'UK',
                'postal_code'      => 'EC1A 1BB',
                'established_date' => '2012-09-01',
                'status'           => 'active',
                'settings'         => ['working_hours' => '09:00-17:30'],
            ],
            [
                'organization_id'  => $globalTech->id,
                'name'             => 'GlobalTech Singapore',
                'code'             => 'GT-SGP',
                'address'          => '1 Marina Blvd, Singapore 018989',
                'phone'            => '+65-0000-0001',
                'email'            => 'singapore@globaltech.io',
                'city'             => 'Singapore',
                'state'            => 'Central Region',
                'country'          => 'Singapore',
                'postal_code'      => '018989',
                'established_date' => '2018-01-15',
                'status'           => 'active',
                'settings'         => ['working_hours' => '09:00-18:00'],
            ],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(['code' => $branch['code']], $branch);
        }

        $this->command->info('Branches seeded.');
    }
}