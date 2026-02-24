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
        $prefix = DocumentPrefix::inRandomOrder()->first();
        return [
            'branch_id' => \App\Models\Branch::factory(),
            'document_category_id' => \App\Models\DocumentCategory::factory(),
            'document_prefix_id' => $prefix?->id ?? 1,
            'created_by' => \App\Models\User::factory(),
            'document_code' => function () use ($prefix) {

                if (!$prefix) {
                    return 'DOC-' . fake()->unique()->numberBetween(1000, 9999);
                }

                $format = $prefix->format_pattern;

                $format = str_replace('YYYY', now()->format('Y'), $format);
                $format = str_replace('MM', now()->format('m'), $format);
                $format = str_replace('DD', now()->format('d'), $format);

                $sequence = str_pad(fake()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);

                $format = str_replace('XXX', $sequence, $format);

                return $prefix->prefix . $format;
            },
            'verification_token' => \Illuminate\Support\Str::uuid(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'expiration_date' => fake()->optional()->date(),

            'verification_status' => 'active',

            'file_path' => null,
            'file_type' => 'pdf',
            'file_size' => fake()->numberBetween(1000, 500000),
            'qr_code_path' => null,
        ];
    }
}
