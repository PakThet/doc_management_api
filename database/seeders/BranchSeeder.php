<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = Organization::query()->get();

        $templates = [
            ['name' => 'Head Office', 'city' => 'Phnom Penh'],
            ['name' => 'Branch A', 'city' => 'Siem Reap'],
            ['name' => 'Branch B', 'city' => 'Battambang'],
        ];

        foreach ($organizations as $organization) {
            foreach ($templates as $template) {
                $code = strtoupper(substr($organization->slug, 0, 3)) . '-' . Str::upper(Str::slug($template['name'], ''));

                Branch::updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'code' => $code,
                    ],
                    [
                        'name' => $template['name'],
                        'address' => $template['city'] . ', Cambodia',
                        'phone' => '+8551000' . str_pad((string) (strlen($template['name']) + $organization->id), 3, '0', STR_PAD_LEFT),
                        'email' => strtolower(str_replace(' ', '.', $template['name'])) . '@' . $organization->slug . '.local',
                        'city' => $template['city'],
                        'country' => 'Cambodia',
                        'status' => 'active',
                        'settings' => ['open_time' => '08:00', 'close_time' => '17:00'],
                    ]
                );
            }
        }
    }
}
