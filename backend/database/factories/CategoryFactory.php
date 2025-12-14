<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'user_id' => User::factory(),
            'notes' => fake()->optional()->sentence(),
            'power_factor' => fake()->randomFloat(2, 0.80, 0.98),
        ];
    }
}
