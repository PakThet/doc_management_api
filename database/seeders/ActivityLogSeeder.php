<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'superadmin@main.com')->first() ?? User::query()->first();

        if (! $user) {
            return;
        }

        activity('seed')
            ->causedBy($user)
            ->withProperties(['source' => 'DatabaseSeeder'])
            ->event('seeded')
            ->log('Initial seed data generated');
    }
}
