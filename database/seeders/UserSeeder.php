<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Municipal Admin Account
        User::create([
            'name' => 'Ratnapura Admin',
            'email' => 'admin@wmis.lk',
            'password' => Hash::make('admin123'), // Encrypted using Bcrypt
            'role' => 'Admin',
            'phone' => '+94711111111',
            'language' => 'en',
        ]);

        // 2. Create Sample Collection Truck Drivers
        User::create([
            'name' => 'K. Silva (Driver 01)',
            'email' => 'silva@wmis.lk',
            'password' => Hash::make('driver123'),
            'role' => 'Driver',
            'phone' => '+94722222222',
            'language' => 'si',
        ]);

        User::create([
            'name' => 'M. Perera (Driver 02)',
            'email' => 'perera@wmis.lk',
            'password' => Hash::make('driver123'),
            'role' => 'Driver',
            'phone' => '+94733333333',
            'language' => 'si',
        ]);

        // 3. Create a Demo Citizen Account
        User::create([
            'name' => 'A. Fernando (Citizen)',
            'email' => 'citizen@wmis.lk',
            'password' => Hash::make('citizen123'),
            'role' => 'Citizen',
            'phone' => '+94744444444',
            'language' => 'en',
        ]);
    }
}