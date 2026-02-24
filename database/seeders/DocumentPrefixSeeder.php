<?php

namespace Database\Seeders;

use App\Models\DocumentPrefix;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentPrefixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DocumentPrefix::insert([
            [
                'name' => 'Human Resource Document',
                'prefix' => 'HR-',
                'format_pattern' => 'YYYY/MM/DD-XXX',
                'description' => 'HR department documents',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Finance Document',
                'prefix' => 'FIN-',
                'format_pattern' => 'YYYY-MM-XXX',
                'description' => 'Finance department documents',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'General Document',
                'prefix' => 'DOC-',
                'format_pattern' => 'YYYY-XXX',
                'description' => 'General company documents',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Internal Memo',
                'prefix' => 'MEMO-',
                'format_pattern' => 'YYYY/MM-XXX',
                'description' => 'Internal memo documents',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

    }
}
