<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentGroup;
use App\Models\DocumentPrefix;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = DocumentCategory::where('status', 'active')->get()->keyBy('slug');
        $branches   = Branch::all();

        foreach ($branches as $branch) {
            $this->seedBranchDocuments($branch, $categories);
        }

        $this->command->info('Documents seeded.');
    }

    private function seedBranchDocuments(Branch $branch, $categories): void
    {
        // Find a staff/admin user belonging to this branch to act as creator
        $creator = User::where('branch_id', $branch->id)->first();
        if (! $creator) {
            return;
        }

        $prefixes = DocumentPrefix::where('status', 'active')->get();

        // Get document groups for this branch
        $groups = DocumentGroup::where('branch_id', $branch->id)->get()->keyBy('name');

        // Map categories to groups
        $categoryToGroupMap = [
            'hr-documents' => 'HR Documents',
            'financial-reports' => 'Financial Reports',
            'policies' => 'Policies',
            'compliance' => 'Compliance',
            'technical-specifications' => 'Contracts',
            'training-materials' => 'Training Materials',
        ];

        $documents = [
            // ── HR Documents ────────────────────────────────────────────────────
            [
                'title'                => 'Employee Handbook ' . date('Y'),
                'description'          => 'Comprehensive guide to company policies, procedures, and benefits for employees.',
                'document_category_id' => $categories['hr-documents']?->id,
                'status'               => 'published',
                'visibility'           => 'restricted',
                'expiration_date'      => now()->addYear()->toDateString(),
                'file_type'            => 'pdf',
                'mime_type'            => 'application/pdf',
                'file_size'            => 2048000,
            ],
            [
                'title'                => 'Remote Work Policy',
                'description'          => 'Guidelines and expectations for remote and hybrid work arrangements.',
                'document_category_id' => $categories['policies']?->id,
                'status'               => 'published',
                'visibility'           => 'restricted',
                'expiration_date'      => now()->addYears(2)->toDateString(),
                'file_type'            => 'pdf',
                'mime_type'            => 'application/pdf',
                'file_size'            => 512000,
            ],
            [
                'title'                => 'Code of Conduct',
                'description'          => 'Expected standards of professional behaviour and ethics.',
                'document_category_id' => $categories['policies']?->id,
                'status'               => 'published',
                'visibility'           => 'public',
                'expiration_date'      => null,
                'file_type'            => 'pdf',
                'mime_type'            => 'application/pdf',
                'file_size'            => 256000,
            ],
            // ── Finance ─────────────────────────────────────────────────────────
            [
                'title'                => 'Q1 ' . date('Y') . ' Financial Report',
                'description'          => 'First quarter financial performance summary.',
                'document_category_id' => $categories['financial-reports']?->id,
                'status'               => 'published',
                'visibility'           => 'private',
                'expiration_date'      => now()->addMonths(6)->toDateString(),
                'file_type'            => 'xlsx',
                'mime_type'            => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size'            => 1024000,
            ],
            [
                'title'                => 'Q2 ' . date('Y') . ' Financial Report',
                'description'          => 'Second quarter financial performance summary.',
                'document_category_id' => $categories['financial-reports']?->id,
                'status'               => 'draft',
                'visibility'           => 'private',
                'expiration_date'      => now()->addMonths(4)->toDateString(),
                'file_type'            => 'xlsx',
                'mime_type'            => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size'            => 980000,
            ],
            // ── Compliance ──────────────────────────────────────────────────────
            [
                'title'                => 'GDPR Compliance Policy',
                'description'          => 'Data privacy and protection compliance documentation.',
                'document_category_id' => $categories['compliance']?->id,
                'status'               => 'published',
                'visibility'           => 'restricted',
                'expiration_date'      => now()->addYears(1)->toDateString(),
                'file_type'            => 'pdf',
                'mime_type'            => 'application/pdf',
                'file_size'            => 768000,
            ],
            [
                'title'                => 'ISO 27001 Audit Report',
                'description'          => 'Information security management audit documentation.',
                'document_category_id' => $categories['compliance']?->id,
                'status'               => 'archived',
                'visibility'           => 'private',
                'expiration_date'      => now()->subMonths(3)->toDateString(),
                'file_type'            => 'pdf',
                'mime_type'            => 'application/pdf',
                'file_size'            => 1500000,
            ],
            // ── IT ──────────────────────────────────────────────────────────────
            [
                'title'                => 'System Architecture Overview',
                'description'          => 'High-level design of the platform infrastructure.',
                'document_category_id' => $categories['technical-specifications']?->id,
                'status'               => 'published',
                'visibility'           => 'private',
                'expiration_date'      => null,
                'file_type'            => 'pdf',
                'mime_type'            => 'application/pdf',
                'file_size'            => 3072000,
            ],
            [
                'title'                => 'API Integration Guide v2',
                'description'          => 'Developer guide for integrating with the public API.',
                'document_category_id' => $categories['technical-specifications']?->id,
                'status'               => 'published',
                'visibility'           => 'public',
                'expiration_date'      => null,
                'file_type'            => 'pdf',
                'mime_type'            => 'application/pdf',
                'file_size'            => 1800000,
            ],
            // ── Training ────────────────────────────────────────────────────────
            [
                'title'                => 'New Employee Onboarding Guide',
                'description'          => 'Step-by-step onboarding process for new joiners.',
                'document_category_id' => $categories['training-materials']?->id,
                'status'               => 'published',
                'visibility'           => 'restricted',
                'expiration_date'      => null,
                'file_type'            => 'pdf',
                'mime_type'            => 'application/pdf',
                'file_size'            => 4096000,
            ],
        ];

        $seq = Document::where('branch_id', $branch->id)->count();

        foreach ($documents as $doc) {
            $seq++;
            $prefix    = $prefixes->random();
            $docCode   = strtoupper($prefix->prefix . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT));

            // Ensure uniqueness across branches
            if (Document::where('document_code', $docCode)->exists()) {
                $docCode = strtoupper($prefix->prefix . '-' . Str::random(6));
            }

            // Find appropriate group based on category
            $groupId = null;
            if ($doc['document_category_id']) {
                $category = DocumentCategory::find($doc['document_category_id']);
                if ($category && isset($categoryToGroupMap[$category->slug])) {
                    $groupName = $categoryToGroupMap[$category->slug];
                    $group = $groups[$groupName] ?? null;
                    $groupId = $group?->id;
                }
            }

            Document::firstOrCreate(
                ['document_code' => $docCode],
                array_merge($doc, [
                    'branch_id'           => $branch->id,
                    'group_id'            => $groupId,
                    'document_prefix_id'  => $prefix->id,
                    'created_by'          => $creator->id,
                    'updated_by'          => $creator->id,
                    'document_code'       => $docCode,
                    'verification_token'  => (string) Str::uuid(),
                    'qr_token'            => (string) Str::uuid(),
                    'file_name'           => Str::slug($doc['title']) . '.' . ($doc['file_type'] ?? 'pdf'),
                    'file_path'           => 'documents/' . $branch->code . '/' . Str::slug($doc['title']) . '.' . ($doc['file_type'] ?? 'pdf'),
                ])
            );
        }
    }
}