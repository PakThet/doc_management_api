<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            DocumentPrefixSeeder::class,
        ]);
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $org = Organization::factory()->create();
        $branches = Branch::factory(5)->create([
            'organization_id' => $org->id,
        ]);
        $categories = DocumentCategory::factory(5)->create();

        // Create Documents
        Document::factory(20)->create([
            'branch_id' => $branches->random()->id,
            'document_category_id' => $categories->random()->id,
            'created_by' => $admin->id,
        ]);
    }
}
