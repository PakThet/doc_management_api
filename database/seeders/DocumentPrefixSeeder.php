<?php

namespace Database\Seeders;

use App\Models\DocumentPrefix;
use Illuminate\Database\Seeder;

class DocumentPrefixSeeder extends Seeder
{
    public function run(): void
    {
        $departments = \App\Models\Department::query()->get();

        foreach ($departments as $index => $department) {
            $prefix = $index === 0 ? 'DOC' : 'DP' . str_pad((string) $department->id, 2, '0', STR_PAD_LEFT);

            DocumentPrefix::updateOrCreate(
                ['prefix' => $prefix],
                [
                    'department_id' => $department->id,
                    'name' => $department->name . ' Prefix',
                    'separator' => '-',
                    'format' => '{PREFIX}{SEPARATOR}{YEAR}{SEPARATOR}{NUMBER}',
                    'description' => 'Auto-generated seed prefix',
                    'status' => 'active',
                    'is_default' => $index === 0,
                    'metadata' => ['seeded' => true],
                ]
            );
        }
    }
}
