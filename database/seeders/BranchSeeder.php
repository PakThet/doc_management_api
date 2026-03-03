<?php
// database/seeders/BranchSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Organization;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = Organization::all();

        $branchData = [];

        foreach ($organizations as $org) {
            $branches = $this->getBranchesForOrganization($org->name);
            
            foreach ($branches as $branch) {
                $branchData[] = [
                    'organization_id' => $org->id,
                    'name' => $branch['name'],
                    'code' => $branch['code'],
                    'address' => $branch['address'],
                    'phone' => $branch['phone'],
                    'email' => $branch['email'],
                    'city' => $branch['city'],
                    'state' => $branch['state'],
                    'country' => $branch['country'],
                    'postal_code' => $branch['postal_code'],
                    'established_date' => $branch['established_date'],
                    'status' => $branch['status'],
                    'settings' => $branch['settings'] ?? null,
                ];
            }
        }

        foreach ($branchData as $branch) {
            Branch::create($branch);
        }

        $this->command->info('Branches created successfully!');
    }

    private function getBranchesForOrganization($orgName): array
    {
        $branches = [
            'TechCorp International' => [
                [
                    'name' => 'Headquarters - San Francisco',
                    'code' => 'SF-HQ',
                    'address' => '123 Silicon Valley, San Francisco, CA',
                    'phone' => '+1-555-123-1001',
                    'email' => 'sf@techcorp.com',
                    'city' => 'San Francisco',
                    'state' => 'California',
                    'country' => 'USA',
                    'postal_code' => '94105',
                    'established_date' => '2010-01-15',
                    'status' => 'active',
                ],
                [
                    'name' => 'New York Office',
                    'code' => 'NYC',
                    'address' => '456 Madison Avenue, New York, NY',
                    'phone' => '+1-555-123-2001',
                    'email' => 'nyc@techcorp.com',
                    'city' => 'New York',
                    'state' => 'New York',
                    'country' => 'USA',
                    'postal_code' => '10022',
                    'established_date' => '2015-03-20',
                    'status' => 'active',
                ],
                [
                    'name' => 'Austin Tech Hub',
                    'code' => 'AUS',
                    'address' => '789 Innovation Blvd, Austin, TX',
                    'phone' => '+1-555-123-3001',
                    'email' => 'austin@techcorp.com',
                    'city' => 'Austin',
                    'state' => 'Texas',
                    'country' => 'USA',
                    'postal_code' => '78701',
                    'established_date' => '2018-06-10',
                    'status' => 'active',
                ],
            ],
            'Global Solutions Ltd' => [
                [
                    'name' => 'London Headquarters',
                    'code' => 'LON-HQ',
                    'address' => '123 Canary Wharf, London',
                    'phone' => '+44-20-1234-1000',
                    'email' => 'london@globalsolutions.com',
                    'city' => 'London',
                    'state' => 'Greater London',
                    'country' => 'UK',
                    'postal_code' => 'E14 5AB',
                    'established_date' => '2008-05-01',
                    'status' => 'active',
                ],
                [
                    'name' => 'Manchester Office',
                    'code' => 'MAN',
                    'address' => '456 Business Park, Manchester',
                    'phone' => '+44-161-234-5678',
                    'email' => 'manchester@globalsolutions.com',
                    'city' => 'Manchester',
                    'state' => 'Greater Manchester',
                    'country' => 'UK',
                    'postal_code' => 'M1 1AE',
                    'established_date' => '2012-08-15',
                    'status' => 'active',
                ],
                [
                    'name' => 'Edinburgh Branch',
                    'code' => 'EDI',
                    'address' => '789 Royal Mile, Edinburgh',
                    'phone' => '+44-131-345-6789',
                    'email' => 'edinburgh@globalsolutions.com',
                    'city' => 'Edinburgh',
                    'state' => 'Scotland',
                    'country' => 'UK',
                    'postal_code' => 'EH1 1RE',
                    'established_date' => '2016-11-20',
                    'status' => 'inactive',
                ],
            ],
            'InnovateTech Asia' => [
                [
                    'name' => 'Singapore HQ',
                    'code' => 'SIN-HQ',
                    'address' => '1 Raffles Place, Singapore',
                    'phone' => '+65-6789-1000',
                    'email' => 'singapore@innovatetech.asia',
                    'city' => 'Singapore',
                    'state' => 'Central Region',
                    'country' => 'Singapore',
                    'postal_code' => '048616',
                    'established_date' => '2014-02-10',
                    'status' => 'active',
                ],
                [
                    'name' => 'Kuala Lumpur Office',
                    'code' => 'KUL',
                    'address' => '456 KLCC, Kuala Lumpur',
                    'phone' => '+60-3-1234-5678',
                    'email' => 'kl@innovatetech.asia',
                    'city' => 'Kuala Lumpur',
                    'state' => 'Federal Territory',
                    'country' => 'Malaysia',
                    'postal_code' => '50088',
                    'established_date' => '2016-05-20',
                    'status' => 'active',
                ],
                [
                    'name' => 'Jakarta Branch',
                    'code' => 'JKT',
                    'address' => '789 Sudirman, Jakarta',
                    'phone' => '+62-21-2345-6789',
                    'email' => 'jakarta@innovatetech.asia',
                    'city' => 'Jakarta',
                    'state' => 'Jakarta',
                    'country' => 'Indonesia',
                    'postal_code' => '12190',
                    'established_date' => '2018-08-15',
                    'status' => 'active',
                ],
            ],
            'MediCare Health Systems' => [
                [
                    'name' => 'Boston Medical Center',
                    'code' => 'BOS-MC',
                    'address' => '123 Longwood Avenue, Boston, MA',
                    'phone' => '+1-555-987-1000',
                    'email' => 'boston@medicarehealth.com',
                    'city' => 'Boston',
                    'state' => 'Massachusetts',
                    'country' => 'USA',
                    'postal_code' => '02115',
                    'established_date' => '2011-07-01',
                    'status' => 'active',
                ],
                [
                    'name' => 'Chicago Regional Office',
                    'code' => 'CHI',
                    'address' => '456 Michigan Avenue, Chicago, IL',
                    'phone' => '+1-555-987-2000',
                    'email' => 'chicago@medicarehealth.com',
                    'city' => 'Chicago',
                    'state' => 'Illinois',
                    'country' => 'USA',
                    'postal_code' => '60611',
                    'established_date' => '2013-09-15',
                    'status' => 'active',
                ],
            ],
            'EduWorld Learning' => [
                [
                    'name' => 'Sydney Campus',
                    'code' => 'SYD',
                    'address' => '123 George Street, Sydney',
                    'phone' => '+61-2-9876-1000',
                    'email' => 'sydney@eduworld.com',
                    'city' => 'Sydney',
                    'state' => 'New South Wales',
                    'country' => 'Australia',
                    'postal_code' => '2000',
                    'established_date' => '2015-03-10',
                    'status' => 'active',
                ],
                [
                    'name' => 'Melbourne Campus',
                    'code' => 'MEL',
                    'address' => '456 Collins Street, Melbourne',
                    'phone' => '+61-3-8765-4321',
                    'email' => 'melbourne@eduworld.com',
                    'city' => 'Melbourne',
                    'state' => 'Victoria',
                    'country' => 'Australia',
                    'postal_code' => '3000',
                    'established_date' => '2017-06-20',
                    'status' => 'inactive',
                ],
            ],
        ];

        return $branches[$orgName] ?? [];
    }
}