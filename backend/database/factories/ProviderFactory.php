<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        return [
            'company_name' => $this->faker->company(),
            'business_registration' => $this->faker->numerify('REG-########'),
            'description' => $this->faker->sentence(),
            'rating' => $this->faker->randomFloat(2, 3.5, 5.0),
            'verified' => true,
            'status' => 'active',
        ];
    }
}
