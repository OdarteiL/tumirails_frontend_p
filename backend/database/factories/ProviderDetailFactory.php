<?php

namespace Database\Factories;

use App\Models\ProviderDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProviderDetailFactory extends Factory
{
    protected $model = ProviderDetail::class;

    public function definition()
    {
        $company = $this->faker->company();

        return [
            'user_id' => User::factory(),
            'company_name' => $company,
            'business_registration' => Str::slug($company) . '-' . $this->faker->unique()->numerify('###'),
            'service_areas' => [],
            'certifications' => [],
            'rating' => $this->faker->randomFloat(2, 3.5, 5.0),
            'verified' => $this->faker->boolean(80),
        ];
    }
}
