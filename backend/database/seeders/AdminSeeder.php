<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@tumi.com')],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'admin123')),
                'phone' => env('ADMIN_PHONE'),
                'address' => env('ADMIN_ADDRESS'),
            ]
        );

        $this->command->info("Admin user created: {$admin->email}");
        $this->command->warn('Remember to change the default password in production!');
    }
}
