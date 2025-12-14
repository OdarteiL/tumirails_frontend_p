<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\TariffStructure;
use App\Models\TariffTier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TariffStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Ghana country
        $ghana = Country::where('code', 'GH')->first();

        if (!$ghana) {
            $this->command->error('Ghana country not found. Please run CountrySeeder first.');
            return;
        }

        // Create Ghana ECG Residential Tariff Structure
        $tariffStructure = TariffStructure::updateOrCreate(
            [
                'country_id' => $ghana->id,
                'name' => 'Ghana ECG Residential',
            ],
            [
                'type' => 'tiered',
                'is_active' => true,
                'effective_date' => '2024-01-01',
                'end_date' => null, // Active indefinitely
            ]
        );

        // Create tariff tiers (ECG Ghana 2024 rates)
        $tiers = [
            [
                'min_kwh' => 0,
                'max_kwh' => 50,
                'rate_per_kwh' => 0.9978, // GH₵0.9978/kWh - Lifeline tariff
                'order' => 1,
            ],
            [
                'min_kwh' => 51,
                'max_kwh' => 300,
                'rate_per_kwh' => 1.2359, // GH₵1.2359/kWh
                'order' => 2,
            ],
            [
                'min_kwh' => 301,
                'max_kwh' => 600,
                'rate_per_kwh' => 1.5449, // GH₵1.5449/kWh
                'order' => 3,
            ],
            [
                'min_kwh' => 601,
                'max_kwh' => null, // Unlimited
                'rate_per_kwh' => 1.8539, // GH₵1.8539/kWh
                'order' => 4,
            ],
        ];

        foreach ($tiers as $tierData) {
            TariffTier::updateOrCreate(
                [
                    'tariff_structure_id' => $tariffStructure->id,
                    'min_kwh' => $tierData['min_kwh'],
                ],
                $tierData
            );
        }

        $this->command->info('Ghana ECG Residential tariff structure and tiers seeded successfully.');
    }
}
