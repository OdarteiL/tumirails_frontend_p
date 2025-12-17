<?php

namespace Database\Seeders;

use App\Models\Appliance;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ApplianceSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin user
        $admin = \App\Models\User::where('role', 'admin')->first();

        if (! $admin) {
            return;
        }

        // Get categories
        $categories = [
            'lighting' => Category::where('name', 'Lighting')->first(),
            'kitchen' => Category::where('name', 'Kitchen Appliances')->first(),
            'entertainment' => Category::where('name', 'Entertainment')->first(),
            'cooling' => Category::where('name', 'Cooling & Heating')->first(),
        ];

        // Skip if categories don't exist
        if (in_array(null, $categories, true)) {
            return;
        }

        // Define public catalog appliances
        $appliances = [
            // Refrigeration
            [
                'name' => 'Refrigerator (Standard)',
                'default_wattage' => 150,
                'category_id' => $categories['kitchen']->id,
                'default_usage_hours' => 24,
                'metadata' => ['efficiency_rating' => 'A+', 'notes' => 'Energy efficient model'],
            ],
            [
                'name' => 'Refrigerator (Large)',
                'default_wattage' => 200,
                'category_id' => $categories['kitchen']->id,
                'default_usage_hours' => 24,
                'metadata' => ['efficiency_rating' => 'A', 'notes' => 'Side-by-side model'],
            ],
            [
                'name' => 'Freezer',
                'default_wattage' => 100,
                'category_id' => $categories['kitchen']->id,
                'default_usage_hours' => 24,
                'metadata' => ['efficiency_rating' => 'A', 'notes' => 'Chest freezer'],
            ],

            // Lighting
            [
                'name' => 'LED Bulb (10W)',
                'default_wattage' => 10,
                'category_id' => $categories['lighting']->id,
                'default_usage_hours' => 6,
                'metadata' => ['efficiency_rating' => 'A+', 'notes' => 'Standard LED bulb'],
            ],
            [
                'name' => 'LED Bulb (15W)',
                'default_wattage' => 15,
                'category_id' => $categories['lighting']->id,
                'default_usage_hours' => 6,
                'metadata' => ['efficiency_rating' => 'A+', 'notes' => 'Bright LED bulb'],
            ],
            [
                'name' => 'Fluorescent Tube',
                'default_wattage' => 40,
                'category_id' => $categories['lighting']->id,
                'default_usage_hours' => 8,
                'metadata' => ['efficiency_rating' => 'B', 'notes' => 'Traditional fluorescent'],
            ],

            // Cooling
            [
                'name' => 'Ceiling Fan',
                'default_wattage' => 75,
                'category_id' => $categories['cooling']->id,
                'default_usage_hours' => 8,
                'metadata' => ['efficiency_rating' => 'A', 'notes' => 'Standard ceiling fan'],
            ],
            [
                'name' => 'Standing Fan',
                'default_wattage' => 50,
                'category_id' => $categories['cooling']->id,
                'default_usage_hours' => 6,
                'metadata' => ['efficiency_rating' => 'A', 'notes' => 'Portable fan'],
            ],
            [
                'name' => 'Air Conditioner (1HP)',
                'default_wattage' => 746,
                'category_id' => $categories['cooling']->id,
                'default_usage_hours' => 8,
                'metadata' => ['efficiency_rating' => 'B', 'notes' => 'Split unit AC'],
            ],
            [
                'name' => 'Air Conditioner (1.5HP)',
                'default_wattage' => 1119,
                'category_id' => $categories['cooling']->id,
                'default_usage_hours' => 8,
                'metadata' => ['efficiency_rating' => 'B', 'notes' => 'Split unit AC'],
            ],

            // Electronics
            [
                'name' => '32" LED TV',
                'default_wattage' => 60,
                'category_id' => $categories['entertainment']->id,
                'default_usage_hours' => 5,
                'metadata' => ['efficiency_rating' => 'A', 'notes' => 'Standard LED TV'],
            ],
            [
                'name' => '55" LED TV',
                'default_wattage' => 120,
                'category_id' => $categories['entertainment']->id,
                'default_usage_hours' => 5,
                'metadata' => ['efficiency_rating' => 'A', 'notes' => 'Large LED TV'],
            ],
            [
                'name' => 'Laptop',
                'default_wattage' => 65,
                'category_id' => $categories['entertainment']->id,
                'default_usage_hours' => 6,
                'metadata' => ['efficiency_rating' => 'A+', 'notes' => 'Standard laptop'],
            ],
            [
                'name' => 'Desktop PC',
                'default_wattage' => 200,
                'category_id' => $categories['entertainment']->id,
                'default_usage_hours' => 6,
                'metadata' => ['efficiency_rating' => 'B', 'notes' => 'Desktop computer with monitor'],
            ],

            // Kitchen
            [
                'name' => 'Microwave',
                'default_wattage' => 800,
                'category_id' => $categories['kitchen']->id,
                'default_usage_hours' => 0.5,
                'metadata' => ['efficiency_rating' => 'B', 'notes' => 'Standard microwave'],
            ],
            [
                'name' => 'Electric Kettle',
                'default_wattage' => 1500,
                'category_id' => $categories['kitchen']->id,
                'default_usage_hours' => 0.5,
                'metadata' => ['efficiency_rating' => 'C', 'notes' => 'Quick boil kettle'],
            ],
            [
                'name' => 'Blender',
                'default_wattage' => 400,
                'category_id' => $categories['kitchen']->id,
                'default_usage_hours' => 0.25,
                'metadata' => ['efficiency_rating' => 'B', 'notes' => 'Kitchen blender'],
            ],
            [
                'name' => 'Toaster',
                'default_wattage' => 1000,
                'category_id' => $categories['kitchen']->id,
                'default_usage_hours' => 0.25,
                'metadata' => ['efficiency_rating' => 'C', 'notes' => '2-slice toaster'],
            ],
            [
                'name' => 'Washing Machine',
                'default_wattage' => 500,
                'category_id' => $categories['kitchen']->id,
                'default_usage_hours' => 1,
                'metadata' => ['efficiency_rating' => 'A', 'notes' => 'Front-load washer'],
            ],
            [
                'name' => 'Iron',
                'default_wattage' => 1200,
                'category_id' => $categories['kitchen']->id,
                'default_usage_hours' => 1,
                'metadata' => ['efficiency_rating' => 'C', 'notes' => 'Steam iron'],
            ],
        ];

        // Seed appliances using updateOrCreate for idempotency
        foreach ($appliances as $appliance) {
            Appliance::withoutGlobalScope('active')->updateOrCreate(
                [
                    'name' => $appliance['name'],
                    'category_id' => $appliance['category_id'],
                ],
                [
                    'default_wattage' => $appliance['default_wattage'],
                    'default_usage_hours' => $appliance['default_usage_hours'],
                    'metadata' => $appliance['metadata'],
                    'is_public' => true,
                    'is_active' => true,
                    'owner_id' => $admin->id,
                    'owner_type' => \App\Models\User::class,
                ]
            );
        }
    }
}
