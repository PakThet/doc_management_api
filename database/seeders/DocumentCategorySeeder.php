<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Contracts',
                'slug'        => 'contracts',
                'description' => 'Legal agreements and contracts between parties.',
                'status'      => 'active',
            ],
            [
                'name'        => 'Policies',
                'slug'        => 'policies',
                'description' => 'Internal company policies and procedures.',
                'status'      => 'active',
            ],
            [
                'name'        => 'HR Documents',
                'slug'        => 'hr-documents',
                'description' => 'Employment records, offer letters, appraisals.',
                'status'      => 'active',
            ],
            [
                'name'        => 'Financial Reports',
                'slug'        => 'financial-reports',
                'description' => 'Invoices, statements, and financial summaries.',
                'status'      => 'active',
            ],
            [
                'name'        => 'Compliance',
                'slug'        => 'compliance',
                'description' => 'Regulatory and audit compliance documents.',
                'status'      => 'active',
            ],
            [
                'name'        => 'Technical Specifications',
                'slug'        => 'technical-specifications',
                'description' => 'System designs, architecture, and API documentation.',
                'status'      => 'active',
            ],
            [
                'name'        => 'Marketing Materials',
                'slug'        => 'marketing-materials',
                'description' => 'Brochures, presentations, and campaign assets.',
                'status'      => 'active',
            ],
            [
                'name'        => 'Training Materials',
                'slug'        => 'training-materials',
                'description' => 'Onboarding guides and training documentation.',
                'status'      => 'active',
            ],
            [
                'name'        => 'Meeting Minutes',
                'slug'        => 'meeting-minutes',
                'description' => 'Records and minutes from official meetings.',
                'status'      => 'active',
            ],
            [
                'name'        => 'Archived',
                'slug'        => 'archived',
                'description' => 'Retired or archived documents.',
                'status'      => 'inactive',
            ],
        ];

        foreach ($categories as $cat) {
            DocumentCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        $this->command->info('Document categories seeded.');
    }
}