<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estimation>
 */
class EstimationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dailyKwh = $this->faker->randomFloat(2, 5, 50);
        $monthlyKwh = $dailyKwh * 30;
        $ratePerKwh = $this->faker->randomFloat(4, 0.10, 0.50);
        
        return [
            'owner_id' => \App\Models\User::factory(),
            'owner_type' => \App\Models\User::class,
            'site_id' => \App\Models\Site::factory(),
            'version' => 1,
            'previous_estimation_id' => null,
            'total_watts' => $this->faker->randomFloat(2, 500, 5000),
            'daily_kwh' => $dailyKwh,
            'monthly_kwh' => $monthlyKwh,
            'estimated_monthly_cost' => $monthlyKwh * $ratePerKwh,
            'tariff_structure_id' => \App\Models\TariffStructure::factory(),
            'power_factor_applied' => $this->faker->randomFloat(2, 0.85, 0.95),
            'seasonal_multiplier' => $this->faker->randomFloat(2, 0.90, 1.15),
            'appliances_snapshot' => [
                [
                    'id' => $this->faker->numberBetween(1, 100),
                    'name' => $this->faker->word(),
                    'watts' => $this->faker->numberBetween(50, 1000),
                    'quantity' => $this->faker->numberBetween(1, 5),
                    'hours_per_day' => $this->faker->numberBetween(1, 24),
                ],
            ],
            'calculation_metadata' => [
                'calculated_at' => now()->toIso8601String(),
                'country_code' => 'GH',
                'location_multiplier' => 1.0,
            ],
            'created_by' => \App\Models\User::factory(),
        ];
    }

    /**
     * Indicate that the estimation is for an organisation.
     */
    public function forOrganisation(): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => \App\Models\Organisation::factory(),
            'owner_type' => \App\Models\Organisation::class,
        ]);
    }

    /**
     * Indicate that the estimation is a newer version.
     */
    public function newVersion(int $previousEstimationId, int $version = 2): static
    {
        return $this->state(fn (array $attributes) => [
            'previous_estimation_id' => $previousEstimationId,
            'version' => $version,
        ]);
    }

}
