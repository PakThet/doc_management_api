<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = Department::all();
        $user = User::first(); // Assuming a user exists

        $positions = [
            // Executive Positions
            ['position_title' => 'Chief Executive Officer', 'level' => 1],
            ['position_title' => 'Chief Operating Officer', 'level' => 1],
            ['position_title' => 'Chief Financial Officer', 'level' => 1],
            ['position_title' => 'Chief Technology Officer', 'level' => 1],

            // HR Positions
            ['position_title' => 'HR Manager', 'level' => 2],
            ['position_title' => 'HR Specialist', 'level' => 3],
            ['position_title' => 'Recruitment Officer', 'level' => 3],

            // Finance Positions
            ['position_title' => 'Finance Manager', 'level' => 2],
            ['position_title' => 'Accountant', 'level' => 3],
            ['position_title' => 'Financial Analyst', 'level' => 3],

            // IT Positions
            ['position_title' => 'IT Manager', 'level' => 2],
            ['position_title' => 'Software Engineer', 'level' => 3],
            ['position_title' => 'System Administrator', 'level' => 3],
            ['position_title' => 'DevOps Engineer', 'level' => 3],

            // Operations Positions
            ['position_title' => 'Operations Manager', 'level' => 2],
            ['position_title' => 'Project Manager', 'level' => 3],
            ['position_title' => 'Business Analyst', 'level' => 3],
        ];

        foreach ($departments as $department) {
            foreach ($positions as $positionData) {
                Position::create([
                    'position_title' => $positionData['position_title'],
                    'department_id' => $department->id,
                    'level' => $positionData['level'],
                    'created_by' => $user->id,
                ]);
            }
        }

        $this->command->info('Positions seeded successfully.');
    }
}