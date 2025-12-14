<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\SeasonalAdjustment;
use Illuminate\Database\Seeder;

class SeasonalAdjustmentSeeder extends Seeder
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

        // Ghana seasonal adjustments
        $adjustments = [
            [
                'country_id' => $ghana->id,
                'season_name' => 'Dry Season',
                'start_month' => 11, // November
                'end_month' => 3,    // March
                'multiplier' => 1.15, // 15% increase due to higher demand
                'description' => 'Dry season with reduced hydroelectric power generation and increased cooling demand',
                'is_active' => true,
            ],
            [
                'country_id' => $ghana->id,
                'season_name' => 'Wet Season',
                'start_month' => 4,  // April
                'end_month' => 10,   // October
                'multiplier' => 1.0, // Normal rates
                'description' => 'Wet season with adequate hydroelectric power generation',
                'is_active' => true,
            ],
        ];

        foreach ($adjustments as $adjustment) {
            SeasonalAdjustment::updateOrCreate(
                [
                    'country_id' => $adjustment['country_id'],
                    'season_name' => $adjustment['season_name'],
                ],
                $adjustment
            );
        }

        $this->command->info('Ghana seasonal adjustments seeded successfully.');
    }
}
