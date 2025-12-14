<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $countries = [
            ['name' => 'Ghana', 'code' => 'GH', 'currency_code' => 'GHS'],
            ['name' => 'Nigeria', 'code' => 'NG', 'currency_code' => 'NGN'],
            ['name' => 'Kenya', 'code' => 'KE', 'currency_code' => 'KES'],
            ['name' => 'South Africa', 'code' => 'ZA', 'currency_code' => 'ZAR'],
            ['name' => 'United States', 'code' => 'US', 'currency_code' => 'USD'],
        ];

        $country = fake()->randomElement($countries);

        return [
            'name' => $country['name'],
            'code' => $country['code'],
            'currency_code' => $country['currency_code'],
            'is_active' => fake()->boolean(90),
        ];
    }
}
