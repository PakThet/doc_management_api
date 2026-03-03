<?php
// database/seeders/OrganizationSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = [
            [
                'name' => 'TechCorp International',
                'slug' => 'techcorp-international',
                'email' => 'info@techcorp.com',
                'phone' => '+1-555-123-4567',
                'address' => '123 Silicon Valley, San Francisco, CA 94105',
                'website' => 'https://techcorp.com',
                'status' => 'active',
                'settings' => [
                    'timezone' => 'America/Los_Angeles',
                    'date_format' => 'Y-m-d',
                    'currency' => 'USD',
                    'language' => 'en',
                ],
            ],
            [
                'name' => 'Global Solutions Ltd',
                'slug' => 'global-solutions',
                'email' => 'contact@globalsolutions.com',
                'phone' => '+44-20-1234-5678',
                'address' => '456 Business Park, London, UK',
                'website' => 'https://globalsolutions.com',
                'status' => 'active',
                'settings' => [
                    'timezone' => 'Europe/London',
                    'date_format' => 'd/m/Y',
                    'currency' => 'GBP',
                    'language' => 'en',
                ],
            ],
            [
                'name' => 'InnovateTech Asia',
                'slug' => 'innovatetech-asia',
                'email' => 'hello@innovatetech.asia',
                'phone' => '+65-6789-0123',
                'address' => '789 Tech Hub, Singapore',
                'website' => 'https://innovatetech.asia',
                'status' => 'active',
                'settings' => [
                    'timezone' => 'Asia/Singapore',
                    'date_format' => 'Y-m-d',
                    'currency' => 'SGD',
                    'language' => 'en',
                ],
            ],
            [
                'name' => 'MediCare Health Systems',
                'slug' => 'medicare-health',
                'email' => 'info@medicarehealth.com',
                'phone' => '+1-555-987-6543',
                'address' => '321 Medical Center, Boston, MA 02115',
                'website' => 'https://medicarehealth.com',
                'status' => 'active',
                'settings' => [
                    'timezone' => 'America/New_York',
                    'date_format' => 'Y-m-d',
                    'currency' => 'USD',
                    'language' => 'en',
                ],
            ],
            [
                'name' => 'EduWorld Learning',
                'slug' => 'eduworld-learning',
                'email' => 'contact@eduworld.com',
                'phone' => '+61-2-9876-5432',
                'address' => '555 Education Street, Sydney, Australia',
                'website' => 'https://eduworld.com',
                'status' => 'inactive',
                'settings' => [
                    'timezone' => 'Australia/Sydney',
                    'date_format' => 'd/m/Y',
                    'currency' => 'AUD',
                    'language' => 'en',
                ],
            ],
        ];

        foreach ($organizations as $org) {
            Organization::create($org);
        }

        $this->command->info('Organizations created successfully!');
    }
}