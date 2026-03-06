<?php
// database/seeders/DepartmentSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Organization;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = Organization::all();

        foreach ($organizations as $org) {
            $departments = $this->getDepartmentsForOrganization($org->name);
            
            foreach ($departments as $dept) {
                Department::create([
                    'organization_id' => $org->id,
                    'name' => $dept['name'],
                    'code' => $dept['code'],
                    'description' => $dept['description'],
                    'parent_id' => null, // Will be updated after employees are created
                    'email' => $dept['email'],
                    'phone' => $dept['phone'],
                    'location' => $dept['location'],
                    'budget' => $dept['budget'],
                    'status' => $dept['status'],
                    'metadata' => $dept['metadata'] ?? null,
                ]);
            }
        }

        $this->command->info('Departments created successfully!');
    }

    private function getDepartmentsForOrganization($orgName): array
    {
        $departments = [
            'TechCorp International' => [
                [
                    'name' => 'Executive Management',
                    'code' => 'EXEC',
                    'description' => 'Executive leadership and strategic planning',
                    'email' => 'executive@techcorp.com',
                    'phone' => '555-123-1000',
                    'location' => 'Floor 15, SF HQ',
                    'budget' => 2500000.00,
                    'status' => 'active',
                    'metadata' => ['level' => 'C-Level', 'priority' => 1],
                ],
                [
                    'name' => 'Engineering',
                    'code' => 'ENG',
                    'description' => 'Software development and engineering',
                    'email' => 'engineering@techcorp.com',
                    'phone' => '555-123-2000',
                    'location' => 'Floors 5-8, SF HQ',
                    'budget' => 5000000.00,
                    'status' => 'active',
                    'metadata' => ['headcount' => 150, 'tech_stack' => ['PHP', 'Python', 'JavaScript']],
                ],
                [
                    'name' => 'Product Management',
                    'code' => 'PM',
                    'description' => 'Product strategy and roadmap',
                    'email' => 'product@techcorp.com',
                    'phone' => '555-123-3000',
                    'location' => 'Floor 9, SF HQ',
                    'budget' => 1500000.00,
                    'status' => 'active',
                    'metadata' => ['products' => 5, 'methodology' => 'Agile'],
                ],
                [
                    'name' => 'Sales',
                    'code' => 'SALES',
                    'description' => 'Sales and business development',
                    'email' => 'sales@techcorp.com',
                    'phone' => '555-123-4000',
                    'location' => 'Floors 3-4, SF HQ',
                    'budget' => 3000000.00,
                    'status' => 'active',
                    'metadata' => ['regions' => ['NA', 'EU', 'APAC']],
                ],
                [
                    'name' => 'Marketing',
                    'code' => 'MKT',
                    'description' => 'Marketing and communications',
                    'email' => 'marketing@techcorp.com',
                    'phone' => '555-123-5000',
                    'location' => 'Floor 10, SF HQ',
                    'budget' => 2000000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Human Resources',
                    'code' => 'HR',
                    'description' => 'Talent acquisition and employee relations',
                    'email' => 'hr@techcorp.com',
                    'phone' => '555-123-6000',
                    'location' => 'Floor 2, SF HQ',
                    'budget' => 800000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Finance',
                    'code' => 'FIN',
                    'description' => 'Financial planning and accounting',
                    'email' => 'finance@techcorp.com',
                    'phone' => '555-123-7000',
                    'location' => 'Floor 11, SF HQ',
                    'budget' => 1200000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'IT Operations',
                    'code' => 'IT',
                    'description' => 'IT infrastructure and support',
                    'email' => 'it@techcorp.com',
                    'phone' => '555-123-8000',
                    'location' => 'Floor 1, SF HQ',
                    'budget' => 1800000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Customer Support',
                    'code' => 'CS',
                    'description' => 'Customer service and technical support',
                    'email' => 'support@techcorp.com',
                    'phone' => '555-123-9000',
                    'location' => 'Floor 12, SF HQ',
                    'budget' => 1500000.00,
                    'status' => 'active',
                ],
            ],
            'Global Solutions Ltd' => [
                [
                    'name' => 'Executive Office',
                    'code' => 'EXEC',
                    'description' => 'Executive management',
                    'email' => 'executive@globalsolutions.com',
                    'phone' => '20-1234-1000',
                    'location' => 'Floor 10, London HQ',
                    'budget' => 2000000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Consulting',
                    'code' => 'CONS',
                    'description' => 'Business consulting services',
                    'email' => 'consulting@globalsolutions.com',
                    'phone' => '20-1234-2000',
                    'location' => 'Floors 5-7, London HQ',
                    'budget' => 4000000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Project Management',
                    'code' => 'PMO',
                    'description' => 'Project management office',
                    'email' => 'pmo@globalsolutions.com',
                    'phone' => '20-1234-3000',
                    'location' => 'Floor 8, London HQ',
                    'budget' => 1200000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Business Development',
                    'code' => 'BD',
                    'description' => 'New business acquisition',
                    'email' => 'bd@globalsolutions.com',
                    'phone' => '20-1234-4000',
                    'location' => 'Floor 9, London HQ',
                    'budget' => 2500000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Research & Development',
                    'code' => 'RND',
                    'description' => 'Market research and innovation',
                    'email' => 'rd@globalsolutions.com',
                    'phone' => '20-1234-5000',
                    'location' => 'Floor 4, London HQ',
                    'budget' => 3000000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Administration',
                    'code' => 'ADMIN',
                    'description' => 'Administrative services',
                    'email' => 'admin@globalsolutions.com',
                    'phone' => '20-1234-6000',
                    'location' => 'Floor 2, London HQ',
                    'budget' => 500000.00,
                    'status' => 'active',
                ],
            ],
            'InnovateTech Asia' => [
                [
                    'name' => 'Leadership',
                    'code' => 'LEAD',
                    'description' => 'Executive leadership team',
                    'email' => 'leadership@innovatetech.asia',
                    'phone' => '6789-1000',
                    'location' => 'Floor 20, Singapore HQ',
                    'budget' => 1800000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Software Development',
                    'code' => 'DEV',
                    'description' => 'Software engineering',
                    'email' => 'dev@innovatetech.asia',
                    'phone' => '6789-2000',
                    'location' => 'Floors 12-15, Singapore HQ',
                    'budget' => 3500000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'AI Research',
                    'code' => 'AI',
                    'description' => 'Artificial intelligence research',
                    'email' => 'ai@innovatetech.asia',
                    'phone' => '6789-3000',
                    'location' => 'Floor 16, Singapore HQ',
                    'budget' => 2800000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Regional Sales',
                    'code' => 'RSALES',
                    'description' => 'Sales across Asia Pacific',
                    'email' => 'sales@innovatetech.asia',
                    'phone' => '6789-4000',
                    'location' => 'Floors 8-10, Singapore HQ',
                    'budget' => 2200000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Partner Relations',
                    'code' => 'PARTNERS',
                    'description' => 'Strategic partnerships',
                    'email' => 'partners@innovatetech.asia',
                    'phone' => '6789-5000',
                    'location' => 'Floor 11, Singapore HQ',
                    'budget' => 1500000.00,
                    'status' => 'active',
                ],
            ],
            'MediCare Health Systems' => [
                [
                    'name' => 'Medical Services',
                    'code' => 'MED',
                    'description' => 'Clinical services and patient care',
                    'email' => 'medical@medicarehealth.com',
                    'phone' => '987-1000',
                    'location' => 'Main Building, Boston',
                    'budget' => 5000000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Nursing',
                    'code' => 'NURSE',
                    'description' => 'Nursing department',
                    'email' => 'nursing@medicarehealth.com',
                    'phone' => '987-2000',
                    'location' => 'Floors 2-5, Boston',
                    'budget' => 3500000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Pharmacy',
                    'code' => 'PHARM',
                    'description' => 'Pharmaceutical services',
                    'email' => 'pharmacy@medicarehealth.com',
                    'phone' => '987-3000',
                    'location' => 'Floor 1, Boston',
                    'budget' => 2800000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Laboratory',
                    'code' => 'LAB',
                    'description' => 'Medical laboratory services',
                    'email' => 'lab@medicarehealth.com',
                    'phone' => '987-4000',
                    'location' => 'Basement, Boston',
                    'budget' => 2200000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Administration',
                    'code' => 'ADMIN',
                    'description' => 'Hospital administration',
                    'email' => 'admin@medicarehealth.com',
                    'phone' => '987-5000',
                    'location' => 'Floor 6, Boston',
                    'budget' => 1800000.00,
                    'status' => 'active',
                ],
            ],
            'EduWorld Learning' => [
                [
                    'name' => 'Academic Affairs',
                    'code' => 'ACAD',
                    'description' => 'Academic programs and curriculum',
                    'email' => 'academic@eduworld.com',
                    'phone' => '9876-1000',
                    'location' => 'Building A, Sydney',
                    'budget' => 2200000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'Student Services',
                    'code' => 'STUDENT',
                    'description' => 'Student support and services',
                    'email' => 'students@eduworld.com',
                    'phone' => '9876-2000',
                    'location' => 'Building B, Sydney',
                    'budget' => 1500000.00,
                    'status' => 'active',
                ],
                [
                    'name' => 'International Programs',
                    'code' => 'INTL',
                    'description' => 'International student programs',
                    'email' => 'international@eduworld.com',
                    'phone' => '9876-3000',
                    'location' => 'Building A, Sydney',
                    'budget' => 1200000.00,
                    'status' => 'inactive',
                ],
            ],
        ];

        return $departments[$orgName] ?? [];
    }
}