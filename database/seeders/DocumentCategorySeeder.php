<?php
// database/seeders/DocumentCategorySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentCategory;
use App\Models\Organization;
use Illuminate\Support\Str;

class DocumentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $organizations = Organization::all();

        $categories = [
            [
                'name' => 'Contracts',
                'description' => 'Legal contracts and agreements',
                'status' => 'active',
                'is_system' => true,
            ],
            [
                'name' => 'Policies',
                'description' => 'Company policies and procedures',
                'status' => 'active',
                'is_system' => true,
            ],
            [
                'name' => 'Employee Records',
                'description' => 'Employee personal and professional records',
                'status' => 'active',
                'is_system' => true,
            ],
            [
                'name' => 'Financial Reports',
                'description' => 'Financial statements and reports',
                'status' => 'active',
                'is_system' => true,
            ],
            [
                'name' => 'Project Documents',
                'description' => 'Project plans, specifications, and reports',
                'status' => 'active',
                'is_system' => false,
            ],
            [
                'name' => 'Technical Documentation',
                'description' => 'Technical specifications and manuals',
                'status' => 'active',
                'is_system' => false,
            ],
            [
                'name' => 'Training Materials',
                'description' => 'Employee training and development materials',
                'status' => 'active',
                'is_system' => false,
            ],
            [
                'name' => 'Meeting Minutes',
                'description' => 'Minutes of meetings and discussions',
                'status' => 'active',
                'is_system' => false,
            ],
            [
                'name' => 'Marketing Collateral',
                'description' => 'Marketing and promotional materials',
                'status' => 'active',
                'is_system' => false,
            ],
            [
                'name' => 'Legal Documents',
                'description' => 'Legal filings and correspondence',
                'status' => 'active',
                'is_system' => true,
            ],
            [
                'name' => 'HR Forms',
                'description' => 'Human resources forms and templates',
                'status' => 'active',
                'is_system' => true,
            ],
            [
                'name' => 'Certificates',
                'description' => 'Certificates and licenses',
                'status' => 'active',
                'is_system' => false,
            ],
        ];

        foreach ($organizations as $org) {
            foreach ($categories as $category) {
                DocumentCategory::create([
                    'organization_id' => $org->id,
                    'name' => $category['name'],
                    'slug' => Str::slug($category['name']) . '-' . $org->id,
                    'description' => $category['description'],
                    'status' => $category['status'],
                    'is_system' => $category['is_system'],
                ]);
            }
        }

        $this->command->info('Document categories created successfully!');
    }
}