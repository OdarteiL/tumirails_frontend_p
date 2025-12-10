<?php

namespace Database\Factories;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisationFactory extends Factory
{
    protected $model = Organisation::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'type' => fake()->randomElement(['installer', 'provider', 'customer']),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
        ];
    }

    public function installer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'installer',
        ]);
    }

    public function provider(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'provider',
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'customer',
        ]);
    }
}
