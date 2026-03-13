<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Database\Seeder;

class EmployeeDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();

        $documents = [
            [
                'file_name' => 'resume_john_doe.pdf',
                'file_type' => 'pdf',
                'file_size' => 102400,
                'mime_type' => 'application/pdf',
                'file_path' => 'employee_documents/resume_john_doe.pdf',
                'expiry_date' => now()->addYear(),
            ],
            [
                'file_name' => 'id_card_john_doe.jpg',
                'file_type' => 'jpg',
                'file_size' => 51200,
                'mime_type' => 'image/jpeg',
                'file_path' => 'employee_documents/id_card_john_doe.jpg',
                'expiry_date' => now()->addYears(5),
            ],
            [
                'file_name' => 'contract_john_doe.pdf',
                'file_type' => 'pdf',
                'file_size' => 204800,
                'mime_type' => 'application/pdf',
                'file_path' => 'employee_documents/contract_john_doe.pdf',
                'expiry_date' => now()->addYears(2),
            ],
        ];

        foreach ($employees as $employee) {
            foreach ($documents as $docData) {
                EmployeeDocument::create([
                    'employee_id' => $employee->id,
                    'file_name' => $docData['file_name'],
                    'file_type' => $docData['file_type'],
                    'file_size' => $docData['file_size'],
                    'mime_type' => $docData['mime_type'],
                    'file_path' => $docData['file_path'],
                    'expiry_date' => $docData['expiry_date'],
                ]);
            }
        }

        $this->command->info('Employee documents seeded successfully.');
    }
}