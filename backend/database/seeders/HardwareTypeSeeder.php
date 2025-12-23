<?php

namespace Database\Seeders;

use App\Models\HardwareType;
use Illuminate\Database\Seeder;

class HardwareTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'key' => 'solar_panel',
                'name' => 'Solar Panel',
                'notes' => 'Photovoltaic panels for converting sunlight to electricity. Specify power rating (W), efficiency (%), voltage, and dimensions.',
            ],
            [
                'key' => 'inverter',
                'name' => 'Inverter',
                'notes' => 'Converts DC power from panels/batteries to AC power. Specify power rating (kW), input/output voltage, and efficiency.',
            ],
            [
                'key' => 'battery',
                'name' => 'Battery',
                'notes' => 'Energy storage system. Specify capacity (kWh), voltage, chemistry type, and cycle life.',
            ],
            [
                'key' => 'charge_controller',
                'name' => 'Charge Controller',
                'notes' => 'Regulates power flow from panels to batteries. Specify current rating (A), type (PWM/MPPT), and voltage compatibility.',
            ],
        ];

        foreach ($types as $type) {
            HardwareType::updateOrCreate(
                ['key' => $type['key']],
                $type
            );
        }
    }
}
