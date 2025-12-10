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
            ['name' => 'Lighting', 'notes' => 'Light bulbs, LED strips, etc.'],
            ['name' => 'Kitchen Appliances', 'notes' => 'Refrigerators, microwaves, etc.'],
            ['name' => 'Entertainment', 'notes' => 'TVs, sound systems, etc.'],
            ['name' => 'Cooling & Heating', 'notes' => 'Air conditioners, fans, heaters'],
            ['name' => 'Computing', 'notes' => 'Computers, laptops, printers'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'user_id' => $admin->id,
                ...$category,
            ]);
        }
    }
}
