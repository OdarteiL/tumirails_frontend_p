<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeasonalAdjustment>
 */
class SeasonalAdjustmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $seasons = [
            ['name' => 'Dry Season', 'start' => 11, 'end' => 3, 'multiplier' => 1.15],
            ['name' => 'Wet Season', 'start' => 4, 'end' => 10, 'multiplier' => 1.00],
            ['name' => 'Summer', 'start' => 6, 'end' => 8, 'multiplier' => 1.20],
            ['name' => 'Winter', 'start' => 12, 'end' => 2, 'multiplier' => 1.10],
        ];

        $season = fake()->randomElement($seasons);

        return [
            'country_id' => Country::factory(),
            'season_name' => $season['name'],
            'start_month' => $season['start'],
            'end_month' => $season['end'],
            'multiplier' => $season['multiplier'],
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(80),
        ];
    }

    /**
     * Ghana dry season (Nov-Mar, 1.15x multiplier).
     */
    public function ghanaDrySeason(): static
    {
        return $this->state(fn (array $attributes) => [
            'season_name' => 'Dry Season',
            'start_month' => 11,
            'end_month' => 3,
            'multiplier' => 1.15,
            'description' => 'Ghana dry season with higher electricity demand',
        ]);
    }

    /**
     * Ghana wet season (Apr-Oct, 1.0x multiplier).
     */
    public function ghanaWetSeason(): static
    {
        return $this->state(fn (array $attributes) => [
            'season_name' => 'Wet Season',
            'start_month' => 4,
            'end_month' => 10,
            'multiplier' => 1.00,
            'description' => 'Ghana wet season with normal electricity demand',
        ]);
    }
}
