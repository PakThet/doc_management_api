<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = [
            [
                'name'     => 'Acme Corporation',
                'slug'     => 'acme-corporation',
                'email'    => 'info@acme.com',
                'phone'    => '+1-800-000-0001',
                'address'  => '1 Acme Blvd, New York, NY 10001',
                'website'  => 'https://acme.com',
                'status'   => 'active',
                'settings' => ['timezone' => 'America/New_York', 'currency' => 'USD'],
            ],
            [
                'name'     => 'Global Tech Ltd',
                'slug'     => 'global-tech-ltd',
                'email'    => 'hello@globaltech.io',
                'phone'    => '+44-20-0000-0001',
                'address'  => '10 Tech Square, London, EC1A 1BB',
                'website'  => 'https://globaltech.io',
                'status'   => 'active',
                'settings' => ['timezone' => 'Europe/London', 'currency' => 'GBP'],
            ],
        ];

        foreach ($organizations as $org) {
            Organization::firstOrCreate(['slug' => $org['slug']], $org);
        }

        $this->command->info('Organizations seeded.');
    }
}