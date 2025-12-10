<?php

namespace Database\Factories;

use App\Models\Appliance;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteApplianceFactory extends Factory
{
    protected $model = SiteAppliance::class;

    public function definition(): array
    {
        return [
            'added_by_id' => User::factory(),
            'added_by_type' => User::class,
            'site_id' => Site::factory(),
            'appliance_id' => Appliance::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'daily_usage_hours' => fake()->randomFloat(2, 0, 24),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
