<?php

namespace Database\Factories;

use App\Models\Appliance;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserApplianceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'site_id' => Site::factory(),
            'appliance_id' => Appliance::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'daily_usage_hours' => fake()->randomFloat(2, 0, 24),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
