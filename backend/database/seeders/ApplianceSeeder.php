<?php

namespace Database\Seeders;

use App\Models\Appliance;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApplianceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        if (! $admin) {
            return;
        }

        $lighting = Category::where('name', 'Lighting')->first();
        $kitchen = Category::where('name', 'Kitchen Appliances')->first();
        $entertainment = Category::where('name', 'Entertainment')->first();
        $cooling = Category::where('name', 'Cooling & Heating')->first();

        if (! $lighting || ! $kitchen || ! $entertainment || ! $cooling) {
            return;
        }

        $appliances = [
            ['name' => 'LED Bulb (10W)', 'default_wattage' => 10, 'category_id' => $lighting->id],
            ['name' => 'LED Bulb (15W)', 'default_wattage' => 15, 'category_id' => $lighting->id],
            ['name' => 'Refrigerator', 'default_wattage' => 150, 'category_id' => $kitchen->id],
            ['name' => 'Microwave', 'default_wattage' => 800, 'category_id' => $kitchen->id],
            ['name' => '32" LED TV', 'default_wattage' => 60, 'category_id' => $entertainment->id],
            ['name' => '55" LED TV', 'default_wattage' => 120, 'category_id' => $entertainment->id],
            ['name' => 'Ceiling Fan', 'default_wattage' => 75, 'category_id' => $cooling->id],
            ['name' => 'Air Conditioner (1HP)', 'default_wattage' => 746, 'category_id' => $cooling->id],
        ];

        foreach ($appliances as $appliance) {
            Appliance::create([
                'owner_id' => $admin->id,
                'owner_type' => User::class,
                ...$appliance,
            ]);
        }
    }
}
