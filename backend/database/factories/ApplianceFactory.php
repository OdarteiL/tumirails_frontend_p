<?php

namespace Database\Factories;

use App\Models\Appliance;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplianceFactory extends Factory
{
    protected $model = Appliance::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'owner_type' => User::class,
            'name' => fake()->words(3, true),
            'default_wattage' => fake()->randomFloat(2, 5, 1000),
            'category_id' => Category::factory(),
        ];
    }
}
