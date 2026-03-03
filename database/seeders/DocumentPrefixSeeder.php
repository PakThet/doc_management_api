<?php
// database/seeders/DocumentPrefixSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentPrefix;
use App\Models\Organization;

class DocumentPrefixSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = Organization::all();

        $prefixes = [
            [
                'name' => 'Contract Prefix',
                'prefix' => 'CTR',
                'separator' => '-',
                'format' => '{prefix}{separator}{year}{separator}{number}',
                'description' => 'For contract documents',
                'status' => 'active',
                'is_default' => false,
            ],
            [
                'name' => 'Invoice Prefix',
                'prefix' => 'INV',
                'separator' => '-',
                'format' => '{prefix}{separator}{year}{month}{separator}{number}',
                'description' => 'For invoices',
                'status' => 'active',
                'is_default' => false,
            ],
            [
                'name' => 'Policy Prefix',
                'prefix' => 'POL',
                'separator' => '/',
                'format' => '{prefix}{separator}{year}{separator}{number}',
                'description' => 'For policy documents',
                'status' => 'active',
                'is_default' => false,
            ],
            [
                'name' => 'Employee Document Prefix',
                'prefix' => 'EMP',
                'separator' => '-',
                'format' => '{prefix}{separator}{year}{separator}{number}',
                'description' => 'For employee-related documents',
                'status' => 'active',
                'is_default' => true,
            ],
            [
                'name' => 'Project Document Prefix',
                'prefix' => 'PRJ',
                'separator' => '-',
                'format' => '{prefix}{separator}{year}{separator}{number}',
                'description' => 'For project documents',
                'status' => 'active',
                'is_default' => false,
            ],
            [
                'name' => 'Financial Document Prefix',
                'prefix' => 'FIN',
                'separator' => '-',
                'format' => '{prefix}{separator}{year}{month}{separator}{number}',
                'description' => 'For financial documents',
                'status' => 'active',
                'is_default' => false,
            ],
            [
                'name' => 'Legal Document Prefix',
                'prefix' => 'LEG',
                'separator' => '-',
                'format' => '{prefix}{separator}{year}{separator}{number}',
                'description' => 'For legal documents',
                'status' => 'active',
                'is_default' => false,
            ],
            [
                'name' => 'Training Material Prefix',
                'prefix' => 'TRN',
                'separator' => '-',
                'format' => '{prefix}{separator}{year}{separator}{number}',
                'description' => 'For training materials',
                'status' => 'inactive',
                'is_default' => false,
            ],
        ];

        foreach ($organizations as $org) {
            foreach ($prefixes as $index => $prefix) {
                DocumentPrefix::create([
                    'organization_id' => $org->id,
                    'name' => $prefix['name'],
                    'prefix' => $prefix['prefix'] . $org->id,
                    'separator' => $prefix['separator'],
                    'format' => $prefix['format'],
                    'description' => $prefix['description'],
                    'status' => $prefix['status'],
                    'is_default' => $index === 0 ? true : $prefix['is_default'], // Make first one default
                    'metadata' => [
                        'type' => explode(' ', $prefix['name'])[0],
                        'created_by' => 'system',
                    ],
                ]);
            }
        }

        $this->command->info('Document prefixes created successfully!');
    }
}