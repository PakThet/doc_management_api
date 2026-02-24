<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'name' => fake()->company(),
        'type' => fake()->randomElement(['Private','Government','NGO']),
        'logo' => null,
        'address' => fake()->address(),
        'phone' => fake()->phoneNumber(),
        'email' => fake()->unique()->companyEmail(),
    ];
    }
}
