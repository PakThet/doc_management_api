<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'organization_id' => \App\Models\Organization::factory(),
        'name' => fake()->city() . ' Branch',
        'address' => fake()->address(),
        'phone' => fake()->phoneNumber(),
        'email' => fake()->unique()->safeEmail(),
        'established_date' => fake()->date(),
    ];

    }
}
