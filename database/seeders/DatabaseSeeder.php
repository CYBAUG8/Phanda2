<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | CATEGORY (Shared)
        |--------------------------------------------------------------------------
        */
        $categoryId = (string) Str::uuid();

        DB::table('categories')->insert([
            'id' => $categoryId,
            'name' => 'Plumbing',
            'slug' => 'plumbing',
            'icon' => 'fa-wrench',
            'description' => 'Plumbing repair and installation services',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | PROVIDERS
        |--------------------------------------------------------------------------
        */
        $providers = [
            [
                'name' => 'Thabo Mokoena',
                'email' => 'thabo@example.com',
                'business' => 'Thabo Plumbing',
                'lat' => -25.7316,
                'lng' => 28.1610,
            ],
            [
                'name' => 'Sipho Dlamini',
                'email' => 'sipho@example.com',
                'business' => 'Sipho FixIt Services',
                'lat' => -25.7280,
                'lng' => 28.1580,
            ],
            [
                'name' => 'Mandla Nkosi',
                'email' => 'mandla@example.com',
                'business' => 'Nkosi Plumbing Solutions',
                'lat' => -25.7350,
                'lng' => 28.1650,
            ],
        ];

        foreach ($providers as $provider) {
            $userId = (string) Str::uuid();
            $providerId = (string) Str::uuid();
            $serviceId = (string) Str::uuid();

            // USER
            DB::table('users')->insert([
                'user_id' => $userId,
                'full_name' => $provider['name'],
                'email' => $provider['email'],
                'phone' => '+27710000000',
                'password' => Hash::make('password123'),
                'role' => 'provider',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // PROFILE
            DB::table('provider_profiles')->insert([
                'provider_id' => $providerId,
                'user_id' => $userId,
                'business_name' => $provider['business'],
                'bio' => 'Trusted plumbing services in Pretoria Gardens & Daspoort.',
                'years_experience' => rand(3, 10),
                'service_area' => 'Pretoria Gardens, Daspoort',
                'kyc_status' => 'APPROVED',
                'is_online' => true,
                'service_radius_km' => 10.00,
                'last_lat' => $provider['lat'],
                'last_lng' => $provider['lng'],
                'rating_avg' => rand(35, 50) / 10, // 3.5 - 5.0
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // SERVICE
            DB::table('services')->insert([
                'service_id' => $serviceId,
                'category_id' => $categoryId,
                'provider_id' => $providerId,
                'provider_name' => $provider['business'],
                'title' => 'Plumbing & Leak Repairs',
                'description' => 'Fix pipes, leaks, drains and geysers.',
                'base_price' => rand(250, 500),
                'min_duration' => 60,
                'location' => 'Pretoria Gardens & Daspoort',
                'rating' => rand(35, 50) / 10,
                'reviews_count' => rand(5, 30),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CUSTOMERS
        |--------------------------------------------------------------------------
        */
        $customers = [
            ['name' => 'Lerato Khumalo', 'email' => 'lerato@example.com'],
            ['name' => 'James Smith', 'email' => 'james@example.com'],
            ['name' => 'Aisha Patel', 'email' => 'aisha@example.com'],
            ['name' => 'Brian Molefe', 'email' => 'brian@example.com'],
        ];

        foreach ($customers as $customer) {
            DB::table('users')->insert([
                'user_id' => (string) Str::uuid(),
                'full_name' => $customer['name'],
                'email' => $customer['email'],
                'phone' => '+27719999999',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}