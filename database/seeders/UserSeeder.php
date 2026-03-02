<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Customer
        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'user_id' => Str::uuid(),
                'full_name' => 'Customer User',
                'phone' => '0811111111',
                'password' => Hash::make('password@123'),
                'role' => 'customer',
            ]
        );

        // Provider
        User::updateOrCreate(
            ['email' => 'provider@example.com'],
            [
                'user_id' => Str::uuid(),
                'full_name' => 'Provider User',
                'phone' => '0822222222',
                'password' => Hash::make('password@123'),
                'role' => 'provider',
            ]
        );

        // Admin
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'user_id' => Str::uuid(),
                'full_name' => 'Admin User',
                'phone' => '0833333333',
                'password' => Hash::make('password@123'),
                'role' => 'admin',
            ]
        );
    }
}
