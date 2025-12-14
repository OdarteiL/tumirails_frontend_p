<?php

namespace Database\Factories;

use App\Models\TariffStructure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TariffTier>
 */
class TariffTierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $minKwh = fake()->randomFloat(2, 0, 500);
        $hasMax = fake()->boolean(70);
        $maxKwh = $hasMax ? fake()->randomFloat(2, $minKwh + 50, $minKwh + 500) : null;

        return [
            'tariff_structure_id' => TariffStructure::factory(),
            'min_kwh' => $minKwh,
            'max_kwh' => $maxKwh,
            'rate_per_kwh' => fake()->randomFloat(4, 0.05, 2.00),
            'order' => fake()->numberBetween(0, 10),
        ];
    }

    /**
     * Indicate this is the first tier (0-50 kWh).
     */
    public function firstTier(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_kwh' => 0,
            'max_kwh' => 50,
            'order' => 0,
        ]);
    }

    /**
     * Indicate this is a middle tier.
     */
    public function middleTier(float $min, float $max, int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'min_kwh' => $min,
            'max_kwh' => $max,
            'order' => $order,
        ]);
    }

    /**
     * Indicate this is the final tier (unlimited).
     */
    public function finalTier(float $min, int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'min_kwh' => $min,
            'max_kwh' => null,
            'order' => $order,
        ]);
    }
}
