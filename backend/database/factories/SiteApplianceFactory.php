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
        $appliance = Appliance::factory()->create();

        return [
            'added_by_id' => User::factory(),
            'added_by_type' => User::class,
            'site_id' => Site::factory(),
            'appliance_id' => $appliance->id,
            'quantity' => fake()->numberBetween(1, 5),
            // Use appliance's default_usage_hours if available, otherwise random
            'daily_usage_hours' => $appliance->default_usage_hours ?? fake()->randomFloat(2, 0, 24),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
