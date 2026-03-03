<?php
// database/seeders/DocumentSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\DocumentCategory;
use App\Models\DocumentPrefix;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = Organization::all();

        foreach ($organizations as $org) {
            $branches = Branch::where('organization_id', $org->id)->pluck('id')->toArray();
            $categories = DocumentCategory::where('organization_id', $org->id)->pluck('id')->toArray();
            $prefixes = DocumentPrefix::where('organization_id', $org->id)->pluck('id')->toArray();
            $users = User::where('organization_id', $org->id)->pluck('id')->toArray();

            // Create sample documents
            $documents = $this->getSampleDocuments($org->name);

            foreach ($documents as $index => $doc) {
                $categoryId = $categories[array_rand($categories)];
                $prefixId = $prefixes[array_rand($prefixes)];
                $branchId = $branches[array_rand($branches)];
                $userId = $users[array_rand($users)];

                // Generate document code based on prefix
                $prefix = DocumentPrefix::find($prefixId);
                $code = $this->generateDocumentCode($prefix, $index + 1);

                Document::create([
                    'organization_id' => $org->id,
                    'branch_id' => $branchId,
                    'document_category_id' => $categoryId,
                    'document_prefix_id' => $prefixId,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'document_code' => $code,
                    'verification_token' => Str::random(64),
                    'qr_token' => Str::random(32),
                    'title' => $doc['title'],
                    'description' => $doc['description'],
                    'expiration_date' => $doc['expiration_date'] ? Carbon::parse($doc['expiration_date']) : null,
                    'status' => $doc['status'],
                    'visibility' => $doc['visibility'],
                    'file_name' => $doc['file_name'],
                    'file_type' => pathinfo($doc['file_name'], PATHINFO_EXTENSION),
                    'file_size' => rand(100000, 5000000),
                    'mime_type' => $this->getMimeType($doc['file_name']),
                    'file_path' => 'documents/' . date('Y/m') . '/' . Str::random(40) . '.' . pathinfo($doc['file_name'], PATHINFO_EXTENSION),
                    'qr_code_path' => 'qrcodes/' . Str::random(32) . '.png',
                ]);
            }
        }

        $this->command->info('Documents created successfully!');
    }

    private function getSampleDocuments($orgName): array
    {
        $documents = [
            // TechCorp International documents
            'TechCorp International' => [
                [
                    'title' => 'Employee Handbook 2024',
                    'description' => 'Complete employee handbook with company policies and procedures',
                    'expiration_date' => '2025-12-31',
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'employee_handbook_2024.pdf',
                ],
                [
                    'title' => 'Q1 2024 Financial Report',
                    'description' => 'Quarterly financial report for Q1 2024',
                    'expiration_date' => null,
                    'status' => 'published',
                    'visibility' => 'restricted',
                    'file_name' => 'q1_2024_financial_report.pdf',
                ],
                [
                    'title' => 'Software Development Guidelines',
                    'description' => 'Best practices and guidelines for software development',
                    'expiration_date' => '2024-12-31',
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'dev_guidelines_v2.1.pdf',
                ],
                [
                    'title' => 'Employee Confidentiality Agreement',
                    'description' => 'Standard confidentiality agreement for all employees',
                    'expiration_date' => null,
                    'status' => 'published',
                    'visibility' => 'private',
                    'file_name' => 'confidentiality_agreement_template.docx',
                ],
                [
                    'title' => 'Annual Leave Policy',
                    'description' => 'Company policy on annual leave and time off',
                    'expiration_date' => '2024-06-30',
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'annual_leave_policy_v3.pdf',
                ],
                [
                    'title' => 'Security Incident Report Template',
                    'description' => 'Template for reporting security incidents',
                    'expiration_date' => null,
                    'status' => 'draft',
                    'visibility' => 'restricted',
                    'file_name' => 'security_incident_template.docx',
                ],
                [
                    'title' => 'Project Alpha Requirements',
                    'description' => 'Requirements specification for Project Alpha',
                    'expiration_date' => '2024-08-15',
                    'status' => 'published',
                    'visibility' => 'restricted',
                    'file_name' => 'project_alpha_requirements_v2.pdf',
                ],
                [
                    'title' => 'Training Schedule Q2 2024',
                    'description' => 'Employee training schedule for Q2 2024',
                    'expiration_date' => '2024-06-30',
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'training_schedule_q2_2024.xlsx',
                ],
                [
                    'title' => 'IT Asset Inventory',
                    'description' => 'Complete inventory of IT assets and equipment',
                    'expiration_date' => null,
                    'status' => 'archived',
                    'visibility' => 'restricted',
                    'file_name' => 'it_asset_inventory_2023.xlsx',
                ],
                [
                    'title' => 'Remote Work Policy',
                    'description' => 'Guidelines for remote and hybrid work arrangements',
                    'expiration_date' => '2024-12-31',
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'remote_work_policy.pdf',
                ],
            ],

            // Global Solutions Ltd documents
            'Global Solutions Ltd' => [
                [
                    'title' => 'Consulting Framework v3',
                    'description' => 'Standard consulting methodology and framework',
                    'expiration_date' => '2025-01-31',
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'consulting_framework_v3.pdf',
                ],
                [
                    'title' => 'Client Onboarding Checklist',
                    'description' => 'Standard checklist for new client onboarding',
                    'expiration_date' => null,
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'client_onboarding_checklist.xlsx',
                ],
                [
                    'title' => 'Market Analysis Report - UK',
                    'description' => 'Comprehensive market analysis for UK region',
                    'expiration_date' => '2024-09-30',
                    'status' => 'published',
                    'visibility' => 'restricted',
                    'file_name' => 'uk_market_analysis_q1_2024.pdf',
                ],
                [
                    'title' => 'Partnership Agreement Template',
                    'description' => 'Standard template for partnership agreements',
                    'expiration_date' => null,
                    'status' => 'published',
                    'visibility' => 'private',
                    'file_name' => 'partnership_agreement_template.docx',
                ],
                [
                    'title' => 'Employee Benefits Summary',
                    'description' => 'Summary of employee benefits and perks',
                    'expiration_date' => '2024-05-31',
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'employee_benefits_2024.pdf',
                ],
            ],

            // InnovateTech Asia documents
            'InnovateTech Asia' => [
                [
                    'title' => 'AI Research Paper - Q1 2024',
                    'description' => 'Research findings on neural networks',
                    'expiration_date' => null,
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'ai_research_q1_2024.pdf',
                ],
                [
                    'title' => 'Product Roadmap 2024-2025',
                    'description' => 'Strategic product roadmap for next 2 years',
                    'expiration_date' => '2025-12-31',
                    'status' => 'published',
                    'visibility' => 'restricted',
                    'file_name' => 'product_roadmap_2024_2025.pptx',
                ],
                [
                    'title' => 'API Documentation v2',
                    'description' => 'Technical documentation for public APIs',
                    'expiration_date' => null,
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'api_documentation_v2.pdf',
                ],
                [
                    'title' => 'Code of Conduct',
                    'description' => 'Company code of conduct and ethics',
                    'expiration_date' => '2024-12-31',
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'code_of_conduct.pdf',
                ],
                [
                    'title' => 'Regional Sales Report - Q1',
                    'description' => 'Sales performance report for Q1 2024',
                    'expiration_date' => '2024-04-30',
                    'status' => 'archived',
                    'visibility' => 'restricted',
                    'file_name' => 'q1_sales_report.pdf',
                ],
            ],

            // MediCare Health Systems documents
            'MediCare Health Systems' => [
                [
                    'title' => 'Patient Safety Guidelines',
                    'description' => 'Comprehensive patient safety protocols',
                    'expiration_date' => '2024-08-31',
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'patient_safety_guidelines.pdf',
                ],
                [
                    'title' => 'Medical Equipment Inventory',
                    'description' => 'Complete inventory of medical equipment',
                    'expiration_date' => null,
                    'status' => 'published',
                    'visibility' => 'restricted',
                    'file_name' => 'medical_equipment_inventory.xlsx',
                ],
                [
                    'title' => 'HIPAA Compliance Manual',
                    'description' => 'Guide to HIPAA compliance and procedures',
                    'expiration_date' => '2024-10-31',
                    'status' => 'published',
                    'visibility' => 'private',
                    'file_name' => 'hipaa_compliance_manual.pdf',
                ],
                [
                    'title' => 'Staff Shift Schedule - April',
                    'description' => 'Monthly staff schedule for April 2024',
                    'expiration_date' => '2024-04-30',
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'april_shift_schedule.xlsx',
                ],
                [
                    'title' => 'Emergency Response Plan',
                    'description' => 'Hospital emergency response procedures',
                    'expiration_date' => null,
                    'status' => 'draft',
                    'visibility' => 'restricted',
                    'file_name' => 'emergency_response_plan.pdf',
                ],
            ],

            // EduWorld Learning documents
            'EduWorld Learning' => [
                [
                    'title' => 'Course Catalog 2024',
                    'description' => 'Complete catalog of courses for 2024',
                    'expiration_date' => '2024-12-31',
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'course_catalog_2024.pdf',
                ],
                [
                    'title' => 'Student Enrollment Form',
                    'description' => 'Standard student enrollment application',
                    'expiration_date' => null,
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'enrollment_form.pdf',
                ],
                [
                    'title' => 'Academic Calendar 2024',
                    'description' => 'Academic year calendar with important dates',
                    'expiration_date' => '2024-12-31',
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'academic_calendar_2024.pdf',
                ],
                [
                    'title' => 'Scholarship Guidelines',
                    'description' => 'Guidelines and criteria for scholarships',
                    'expiration_date' => '2024-06-30',
                    'status' => 'published',
                    'visibility' => 'public',
                    'file_name' => 'scholarship_guidelines.pdf',
                ],
                [
                    'title' => 'International Student Handbook',
                    'description' => 'Handbook for international students',
                    'expiration_date' => null,
                    'status' => 'draft',
                    'visibility' => 'public',
                    'file_name' => 'international_student_handbook.pdf',
                ],
            ],
        ];

        return $documents[$orgName] ?? $this->getDefaultDocuments();
    }

    private function getDefaultDocuments(): array
    {
        return [
            [
                'title' => 'Employee Handbook',
                'description' => 'General employee handbook',
                'expiration_date' => '2024-12-31',
                'status' => 'published',
                'visibility' => 'public',
                'file_name' => 'employee_handbook.pdf',
            ],
            [
                'title' => 'Company Policies',
                'description' => 'General company policies',
                'expiration_date' => null,
                'status' => 'published',
                'visibility' => 'public',
                'file_name' => 'company_policies.pdf',
            ],
            [
                'title' => 'Annual Report 2023',
                'description' => 'Annual report for fiscal year 2023',
                'expiration_date' => null,
                'status' => 'archived',
                'visibility' => 'public',
                'file_name' => 'annual_report_2023.pdf',
            ],
        ];
    }

    private function generateDocumentCode($prefix, $number): string
    {
        $code = $prefix->format;
        $code = str_replace('{prefix}', $prefix->prefix, $code);
        $code = str_replace('{separator}', $prefix->separator, $code);
        $code = str_replace('{year}', now()->format('Y'), $code);
        $code = str_replace('{month}', now()->format('m'), $code);
        $code = str_replace('{day}', now()->format('d'), $code);
        $code = str_replace('{number}', str_pad($number, 5, '0', STR_PAD_LEFT), $code);

        return $code;
    }

    private function getMimeType($filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
