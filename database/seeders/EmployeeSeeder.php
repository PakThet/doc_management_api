<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Organization;
use Faker\Factory as Faker;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $organizations = Organization::all();

        foreach ($organizations as $org) {

            $branches = Branch::where('organization_id', $org->id)->pluck('id')->toArray();
            $departments = Department::where('organization_id', $org->id)->pluck('id')->toArray();
            $users = User::where('organization_id', $org->id)->get();

            // -----------------------------
            // Employees WITH user accounts
            // -----------------------------
            foreach ($users as $index => $user) {

                $joinDate = Carbon::instance(
                    $faker->dateTimeBetween('-10 years', 'now')
                );

                $probationDate = $faker->boolean(70)
                    ? $joinDate->copy()->addMonths(3)
                    : null;

                $confirmationDate = $probationDate && $faker->boolean(60)
                    ? $probationDate->copy()->addMonth()
                    : null;

                Employee::create([
                    'organization_id' => $org->id,
                    'user_id' => $user->id,
                    'branch_id' => $faker->randomElement($branches),
                    'department_id' => $faker->randomElement($departments),
                    'employee_code' => 'EMP'
                        . str_pad($org->id, 2, '0', STR_PAD_LEFT)
                        . str_pad($index + 1, 4, '0', STR_PAD_LEFT),

                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $faker->address(),

                    'status' => $user->status === 'active' ? 'active' : 'inactive',
                    'employment_type' => $faker->randomElement(['full_time', 'part_time', 'contract']),

                    'date_of_birth' => $faker->dateTimeBetween('-60 years', '-20 years')->format('Y-m-d'),
                    'join_date' => $joinDate->format('Y-m-d'),
                    'probation_end_date' => $probationDate?->format('Y-m-d'),
                    'confirmation_date' => $confirmationDate?->format('Y-m-d'),

                    'position' => $faker->jobTitle(),
                    'salary' => $faker->randomFloat(2, 30000, 150000),

                    'emergency_contact_name' => $faker->name(),
                    'emergency_contact_phone' => $faker->phoneNumber(),

                    'bank_details' => [
                        'account_name' => $user->first_name . ' ' . $user->last_name,
                        'account_number' => $faker->bankAccountNumber(),
                        'bank_name' => $faker->randomElement([
                            'Chase',
                            'Bank of America',
                            'Wells Fargo',
                            'HSBC',
                            'Standard Chartered'
                        ]),
                        'branch' => $faker->city(),
                        'routing_number' => $faker->numerify('#########'),
                    ],

                    'documents' => [
                        'resume' => 'resume_' . strtolower($user->first_name) . '.pdf',
                        'contract' => 'contract_' . strtolower($user->first_name) . '.pdf',
                        'id_proof' => 'id_' . strtolower($user->first_name) . '.jpg',
                    ],

                    'metadata' => [
                        'nationality' => $faker->country(),
                        'marital_status' => $faker->randomElement(['single', 'married', 'divorced']),
                        'blood_group' => $faker->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
                        'emergency_contact_relation' => $faker->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend']),
                    ],
                ]);
            }

            // -----------------------------------
            // Additional employees WITHOUT users
            // -----------------------------------
            for ($i = 0; $i < 20; $i++) {

                $firstName = $faker->firstName();
                $lastName = $faker->lastName();

                $joinDate = Carbon::instance(
                    $faker->dateTimeBetween('-5 years', 'now')
                );

                $probationDate = $faker->boolean(70)
                    ? $joinDate->copy()->addMonths(3)
                    : null;

                $confirmationDate = $probationDate && $faker->boolean(60)
                    ? $probationDate->copy()->addMonth()
                    : null;

                Employee::create([
                    'organization_id' => $org->id,
                    'user_id' => null,
                    'branch_id' => $faker->randomElement($branches),
                    'department_id' => $faker->randomElement($departments),

                    'employee_code' => 'EMP'
                        . str_pad($org->id, 2, '0', STR_PAD_LEFT)
                        . str_pad($users->count() + $i + 1, 4, '0', STR_PAD_LEFT),

                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => strtolower($firstName . '.' . $lastName . '@'
                        . str_replace(' ', '', strtolower($org->name)) . '.com'),

                    'phone' => $faker->phoneNumber(),
                    'address' => $faker->address(),

                    'status' => $faker->randomElement(['active', 'active', 'active', 'inactive', 'on_leave']),
                    'employment_type' => $faker->randomElement(['full_time', 'part_time', 'contract', 'intern']),

                    'date_of_birth' => $faker->dateTimeBetween('-60 years', '-20 years')->format('Y-m-d'),
                    'join_date' => $joinDate->format('Y-m-d'),
                    'probation_end_date' => $probationDate?->format('Y-m-d'),
                    'confirmation_date' => $confirmationDate?->format('Y-m-d'),

                    'position' => $faker->jobTitle(),
                    'salary' => $faker->randomFloat(2, 25000, 120000),

                    'emergency_contact_name' => $faker->name(),
                    'emergency_contact_phone' => $faker->phoneNumber(),

                    'bank_details' => [
                        'account_name' => $firstName . ' ' . $lastName,
                        'account_number' => $faker->bankAccountNumber(),
                        'bank_name' => $faker->randomElement([
                            'Chase',
                            'Bank of America',
                            'Wells Fargo',
                            'HSBC',
                            'Standard Chartered'
                        ]),
                        'branch' => $faker->city(),
                    ],

                    'metadata' => [
                        'nationality' => $faker->country(),
                        'marital_status' => $faker->randomElement(['single', 'married']),
                    ],
                ]);
            }
        }

        $this->assignDepartmentHeads();

        $this->command->info('Employees created successfully!');
    }

    private function assignDepartmentHeads(): void
    {
        $departments = Department::all();

        foreach ($departments as $department) {

            $employee = Employee::where('organization_id', $department->organization_id)
                ->where('department_id', $department->id)
                ->inRandomOrder()
                ->first();

            if ($employee) {
                $department->head_of_department_id = $employee->id;
                $department->save();
            }
        }
    }
}