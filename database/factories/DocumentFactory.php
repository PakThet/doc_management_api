<?php

namespace Database\Factories;

use App\Models\DocumentPrefix;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    $prefix = \App\Models\DocumentPrefix::inRandomOrder()->first();

    return [
        'organization_id' => \App\Models\Organization::factory(),
        'branch_id' => \App\Models\Branch::factory(),
        'document_category_id' => \App\Models\DocumentCategory::factory(),
        'document_prefix_id' => $prefix?->id,

        'created_by' => \App\Models\User::factory(),

        'document_code' => 'DOC-' . fake()->unique()->numberBetween(1000, 9999),
        'verification_token' => \Illuminate\Support\Str::uuid(),

        'title' => fake()->sentence(),
        'description' => fake()->paragraph(),

        'expiration_date' => fake()->optional()->date(),

        'status' => 'published',
        'visibility' => 'private',
        'version' => 1,

        'file_name' => 'sample.pdf',
        'file_type' => 'pdf',
        'file_size' => fake()->numberBetween(1000, 500000),
        'mime_type' => 'application/pdf',

        'qr_token' => \Illuminate\Support\Str::random(32),
        'qr_code_path' => null,
    ];
}
}
