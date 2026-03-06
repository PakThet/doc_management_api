<?php
// database/seeders/ActivityLogSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use App\Models\Organization;
use App\Models\Employee;
use App\Models\Document;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $organizations = Organization::all();
        $employees = Employee::all();
        $documents = Document::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Skipping ActivityLogSeeder.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Login Activities
        |--------------------------------------------------------------------------
        */
        foreach ($users as $user) {
            for ($i = 0; $i < rand(5, 20); $i++) {

                Activity::create([
                    'log_name'      => 'auth',
                    'description'   => 'logged_in',
                    'subject_type'  => null,
                    'subject_id'    => null,
                    'causer_type'   => User::class,
                    'causer_id'     => $user->id,
                    'properties'    => [
                        'ip' => '192.168.1.' . rand(1, 255),
                        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                        'login_status' => 'success',
                    ],
                    'created_at'    => Carbon::now()->subDays(rand(0, 30)),
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Document View Activities
        |--------------------------------------------------------------------------
        */
        foreach ($users as $user) {

            if ($documents->isNotEmpty()) {

                $randomDocs = $documents->random(
                    min(rand(3, 10), $documents->count())
                );

                foreach ($randomDocs as $doc) {

                    Activity::create([
                        'log_name'      => 'document',
                        'description'   => 'viewed_document',
                        'subject_type'  => Document::class,
                        'subject_id'    => $doc->id,
                        'causer_type'   => User::class,
                        'causer_id'     => $user->id,
                        'properties'    => [
                            'action' => 'viewed',
                            'document_code' => $doc->document_code,
                        ],
                        'created_at'    => Carbon::now()->subDays(rand(0, 30)),
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Employee Update Activities
        |--------------------------------------------------------------------------
        */
        foreach ($users as $user) {

            if ($employees->isNotEmpty()) {

                $randomEmployees = $employees->random(
                    min(rand(2, 5), $employees->count())
                );

                foreach ($randomEmployees as $emp) {

                    Activity::create([
                        'log_name'      => 'employee',
                        'description'   => 'updated_employee',
                        'subject_type'  => Employee::class,
                        'subject_id'    => $emp->id,
                        'causer_type'   => User::class,
                        'causer_id'     => $user->id,
                        'properties'    => [
                            'action' => 'updated',
                            'employee_name' => $emp->full_name,
                        ],
                        'created_at'    => Carbon::now()->subDays(rand(0, 30)),
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 4️⃣ Organization View Activity
        |--------------------------------------------------------------------------
        */
        foreach ($users as $user) {

            if ($organizations->isNotEmpty()) {

                $org = $organizations->random();

                Activity::create([
                    'log_name'      => 'organization',
                    'description'   => 'viewed_organization',
                    'subject_type'  => Organization::class,
                    'subject_id'    => $org->id,
                    'causer_type'   => User::class,
                    'causer_id'     => $user->id,
                    'properties'    => [
                        'action' => 'viewed',
                        'organization_name' => $org->name,
                    ],
                    'created_at'    => Carbon::now()->subDays(rand(0, 30)),
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 5️⃣ Created & Updated Document Logs
        |--------------------------------------------------------------------------
        */
        foreach ($documents->take(20) as $index => $doc) {

            $user = $users->random();
            $daysAgo = rand(1, 30);

            // Created log
            Activity::create([
                'log_name'      => 'document',
                'description'   => 'created_document',
                'subject_type'  => Document::class,
                'subject_id'    => $doc->id,
                'causer_type'   => User::class,
                'causer_id'     => $user->id,
                'properties'    => [
                    'action' => 'created',
                    'document_code' => $doc->document_code,
                    'title' => $doc->title,
                ],
                'created_at'    => Carbon::now()->subDays($daysAgo),
            ]);

            // Every third document gets update log
            if ($index % 3 === 0) {

                Activity::create([
                    'log_name'      => 'document',
                    'description'   => 'updated_document',
                    'subject_type'  => Document::class,
                    'subject_id'    => $doc->id,
                    'causer_type'   => User::class,
                    'causer_id'     => $user->id,
                    'properties'    => [
                        'action' => 'updated',
                        'changes' => [
                            'status' => ['draft', 'published'],
                        ],
                    ],
                    'created_at'    => Carbon::now()->subDays(rand(1, 15)),
                ]);
            }
        }

        $this->command->info('✅ Activity logs created successfully!');
    }
}