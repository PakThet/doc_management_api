<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\DocumentGroup;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();
        $user = User::first(); // Assuming a user exists

        $groups = [
            ['name' => 'HR Documents', 'description' => 'Human Resources related documents'],
            ['name' => 'Financial Reports', 'description' => 'Financial statements and reports'],
            ['name' => 'Contracts', 'description' => 'Legal contracts and agreements'],
            ['name' => 'Policies', 'description' => 'Company policies and procedures'],
            ['name' => 'Training Materials', 'description' => 'Employee training documents'],
            ['name' => 'Compliance', 'description' => 'Regulatory compliance documents'],
        ];

        foreach ($branches as $branch) {
            foreach ($groups as $groupData) {
                DocumentGroup::create([
                    'branch_id' => $branch->id,
                    'name' => $groupData['name'],
                    'description' => $groupData['description'],
                    'created_by' => $user->id,
                ]);
            }
        }

        $this->command->info('Document groups seeded successfully.');
    }
}