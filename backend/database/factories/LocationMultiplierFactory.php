<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LocationMultiplier>
 */
class LocationMultiplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $regions = [
            ['region' => 'Greater Accra', 'city' => 'Accra', 'multiplier' => 1.00],
            ['region' => 'Ashanti', 'city' => 'Kumasi', 'multiplier' => 0.95],
            ['region' => 'Northern', 'city' => null, 'multiplier' => 0.85],
            ['region' => 'Western', 'city' => 'Takoradi', 'multiplier' => 0.90],
        ];

        $location = fake()->randomElement($regions);

        return [
            'country_id' => Country::factory(),
            'region' => $location['region'],
            'city' => $location['city'],
            'multiplier' => $location['multiplier'],
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(90),
        ];
    }

    /**
     * Urban area (higher multiplier).
     */
    public function urban(): static
    {
        return $this->state(fn (array $attributes) => [
            'multiplier' => fake()->randomFloat(2, 1.00, 1.15),
            'description' => 'Urban area with higher infrastructure costs',
        ]);
    }

    /**
     * Rural area (lower multiplier).
     */
    public function rural(): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => null,
            'multiplier' => fake()->randomFloat(2, 0.80, 0.95),
            'description' => 'Rural area with lower infrastructure costs',
        ]);
    }
}
