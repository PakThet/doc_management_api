<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();

        $achievements = [
            ['title' => 'Employee of the Month', 'achievement_date' => now()->subMonths(2)],
            ['title' => 'Best Team Player', 'achievement_date' => now()->subMonths(4)],
            ['title' => 'Innovation Award', 'achievement_date' => now()->subMonths(6)],
            ['title' => 'Leadership Excellence', 'achievement_date' => now()->subYear()],
        ];

        foreach ($employees as $employee) {
            foreach ($achievements as $achievementData) {
                Achievement::create([
                    'employee_id' => $employee->id,
                    'title' => $achievementData['title'],
                    'achievement_date' => $achievementData['achievement_date'],
                ]);
            }
        }

        $this->command->info('Achievements seeded successfully.');
    }
}