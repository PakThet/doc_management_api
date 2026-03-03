<?php

namespace Database\Factories;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentCategory>
 */
class DocumentCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::first()?->id ?? Organization::factory(),

            'name' => $this->faker->unique()->word(),
            'slug' => $this->faker->unique()->slug(),
        ];

    }
}
