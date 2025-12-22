<?php

namespace Database\Seeders;

use App\Models\Hardware;
use App\Models\HardwareType;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderHardwareSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['company_name' => 'Solar Ghana Ltd', 'rating' => 4.5, 'verified' => true],
            ['company_name' => 'West Africa Solar', 'rating' => 4.2, 'verified' => true],
            ['company_name' => 'Eco Power Systems', 'rating' => 4.7, 'verified' => true],
        ];

        $types = HardwareType::all()->keyBy('key');

        foreach ($providers as $providerData) {
            $provider = Provider::updateOrCreate(
                ['company_name' => $providerData['company_name']],
                array_merge($providerData, ['status' => 'active'])
            );

            // Solar Panels
            $this->createHardware($provider, $types['solar_panel'], [
                ['name' => 'Canadian Solar 450W', 'price' => 1600, 'specs' => ['power_watts' => 450, 'efficiency' => 20.5, 'voltage' => 48]],
                ['name' => 'Longi 550W Mono', 'price' => 1800, 'specs' => ['power_watts' => 550, 'efficiency' => 21.2, 'voltage' => 48]],
                ['name' => 'JA Solar 600W', 'price' => 2000, 'specs' => ['power_watts' => 600, 'efficiency' => 21.8, 'voltage' => 48]],
            ]);

            // Inverters
            $this->createHardware($provider, $types['inverter'], [
                ['name' => 'Growatt 3kW', 'price' => 4500, 'specs' => ['power_kw' => 3, 'efficiency' => 95.5, 'voltage' => '48V']],
                ['name' => 'Growatt 5kW', 'price' => 6500, 'specs' => ['power_kw' => 5, 'efficiency' => 96.2, 'voltage' => '48V']],
                ['name' => 'Deye 8kW', 'price' => 9500, 'specs' => ['power_kw' => 8, 'efficiency' => 96.8, 'voltage' => '48V']],
            ]);

            // Batteries
            $this->createHardware($provider, $types['battery'], [
                ['name' => 'Pylontech 5kWh', 'price' => 8000, 'specs' => ['capacity_kwh' => 5, 'voltage' => '48V', 'chemistry' => 'LiFePO4']],
                ['name' => 'Pylontech 10kWh', 'price' => 15000, 'specs' => ['capacity_kwh' => 10, 'voltage' => '48V', 'chemistry' => 'LiFePO4']],
                ['name' => 'BYD 15kWh', 'price' => 22000, 'specs' => ['capacity_kwh' => 15, 'voltage' => '48V', 'chemistry' => 'LiFePO4']],
            ]);

            // Charge Controllers
            $this->createHardware($provider, $types['charge_controller'], [
                ['name' => 'Victron 60A MPPT', 'price' => 2500, 'specs' => ['current_amps' => 60, 'type' => 'MPPT', 'efficiency' => 98]],
                ['name' => 'Victron 100A MPPT', 'price' => 3500, 'specs' => ['current_amps' => 100, 'type' => 'MPPT', 'efficiency' => 98]],
            ]);
        }
    }

    private function createHardware(Provider $provider, HardwareType $type, array $items): void
    {
        foreach ($items as $item) {
            $priceVariation = rand(-10, 10) / 100;
            Hardware::updateOrCreate(
                ['name' => $item['name'], 'provider_id' => $provider->id],
                [
                    'hardware_type_id' => $type->id,
                    'price' => $item['price'] * (1 + $priceVariation),
                    'currency' => 'GHS',
                    'specs' => $item['specs'],
                    'stock_quantity' => rand(10, 50),
                    'status' => 'active',
                    'verified' => true,
                ]
            );
        }
    }
}
