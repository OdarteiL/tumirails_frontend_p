<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TariffStructure>
 */
class TariffStructureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $effectiveDate = fake()->dateTimeBetween('-1 year', '+1 month');
        $hasEndDate = fake()->boolean(30);

        return [
            'country_id' => Country::factory(),
            'name' => fake()->randomElement([
                'Residential Tariff',
                'Commercial Tariff',
                'Industrial Tariff',
                'Agricultural Tariff',
            ]).' '.fake()->year(),
            'type' => fake()->randomElement(['tiered', 'flat', 'time_of_use']),
            'is_active' => fake()->boolean(80),
            'effective_date' => $effectiveDate,
            'end_date' => $hasEndDate ? fake()->dateTimeBetween($effectiveDate, '+2 years') : null,
        ];
    }

    /**
     * Indicate that the tariff structure is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'effective_date' => now()->subMonths(6),
            'end_date' => now()->addMonths(6),
        ]);
    }

    /**
     * Indicate that the tariff structure is tiered.
     */
    public function tiered(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'tiered',
        ]);
    }

    /**
     * Indicate that the tariff structure is flat.
     */
    public function flat(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'flat',
        ]);
    }

    /**
     * Indicate that the tariff structure is time of use.
     */
    public function timeOfUse(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'time_of_use',
        ]);
    }
}
