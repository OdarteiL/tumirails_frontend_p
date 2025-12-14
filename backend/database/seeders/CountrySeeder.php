<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Ghana',
                'code' => 'GH',
                'currency_code' => 'GHS',
                'is_active' => true,
            ],
            [
                'name' => 'Nigeria',
                'code' => 'NG',
                'currency_code' => 'NGN',
                'is_active' => false,
            ],
            [
                'name' => 'Kenya',
                'code' => 'KE',
                'currency_code' => 'KES',
                'is_active' => false,
            ],
            [
                'name' => 'South Africa',
                'code' => 'ZA',
                'currency_code' => 'ZAR',
                'is_active' => false,
            ],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']],
                $country
            );
        }

        $this->command->info('Countries seeded successfully.');
    }
}
