<?php

namespace Database\Factories;

use App\Models\Estimation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecommendationBundle>
 */
class RecommendationBundleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'estimation_id' => Estimation::factory(),
            'owner_type' => null,
            'owner_id' => null,
            'total_cost' => $this->faker->randomFloat(2, 100, 10000),
            'currency' => 'GHS',
            'metadata' => null,
            'created_by' => null,
        ];
    }
}
