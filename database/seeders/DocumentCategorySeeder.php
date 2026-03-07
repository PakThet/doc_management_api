<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['General', 'HR', 'Finance', 'Legal', 'Operations'];

        foreach ($categories as $name) {
            DocumentCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $name . ' documents',
                    'status' => 'active',
                ]
            );
        }
    }
}
