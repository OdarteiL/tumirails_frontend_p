<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        if (! $admin) {
            return;
        }

        $categories = [
            ['name' => 'Lighting', 'notes' => 'Light bulbs, LED strips, etc.', 'power_factor' => 0.95],
            ['name' => 'Kitchen Appliances', 'notes' => 'Refrigerators, microwaves, etc.', 'power_factor' => 0.88],
            ['name' => 'Entertainment', 'notes' => 'TVs, sound systems, etc.', 'power_factor' => 0.92],
            ['name' => 'Cooling & Heating', 'notes' => 'Air conditioners, fans, heaters', 'power_factor' => 0.85],
            ['name' => 'Computing', 'notes' => 'Computers, laptops, printers', 'power_factor' => 0.92],
        ];

        foreach ($categories as $category) {
            Category::create([
                'user_id' => $admin->id,
                ...$category,
            ]);
        }
    }
}
