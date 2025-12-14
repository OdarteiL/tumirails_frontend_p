<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\LocationMultiplier;
use Illuminate\Database\Seeder;

class LocationMultiplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Ghana country
        $ghana = Country::where('code', 'GH')->first();

        if (! $ghana) {
            $this->command->error('Ghana country not found. Please run CountrySeeder first.');

            return;
        }

        // Ghana location multipliers by region
        $multipliers = [
            [
                'country_id' => $ghana->id,
                'region' => 'Greater Accra',
                'city' => null,
                'multiplier' => 1.00, // Baseline - capital city
                'description' => 'Capital region with standard infrastructure costs',
                'is_active' => true,
            ],
            [
                'country_id' => $ghana->id,
                'region' => 'Ashanti',
                'city' => null,
                'multiplier' => 0.95, // Slightly lower than capital
                'description' => 'Second largest region with good infrastructure',
                'is_active' => true,
            ],
            [
                'country_id' => $ghana->id,
                'region' => 'Northern',
                'city' => null,
                'multiplier' => 0.85, // Lower costs in rural/northern areas
                'description' => 'Rural region with lower infrastructure costs',
                'is_active' => true,
            ],
            [
                'country_id' => $ghana->id,
                'region' => 'Western',
                'city' => null,
                'multiplier' => 0.90, // Moderate costs
                'description' => 'Coastal region with moderate infrastructure costs',
                'is_active' => true,
            ],
            [
                'country_id' => $ghana->id,
                'region' => 'Eastern',
                'city' => null,
                'multiplier' => 0.92,
                'description' => 'Eastern region with moderate costs',
                'is_active' => true,
            ],
        ];

        foreach ($multipliers as $multiplier) {
            LocationMultiplier::updateOrCreate(
                [
                    'country_id' => $multiplier['country_id'],
                    'region' => $multiplier['region'],
                    'city' => $multiplier['city'],
                ],
                $multiplier
            );
        }

        $this->command->info('Ghana location multipliers seeded successfully.');
    }
}
