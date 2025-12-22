<?php

namespace Database\Factories;

use App\Models\Estimation;
use App\Models\Hardware;
use App\Models\RecommendedHardware;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecommendedHardwareFactory extends Factory
{
    protected $model = RecommendedHardware::class;

    public function definition(): array
    {
        return [
            'estimation_id' => Estimation::factory(),
            'hardware_id' => Hardware::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'total_cost' => $this->faker->randomFloat(2, 500, 10000),
            'currency' => 'GHS',
            'recommendation_rank' => $this->faker->numberBetween(1, 5),
            'rationale' => $this->faker->sentence(),
        ];
    }
}
