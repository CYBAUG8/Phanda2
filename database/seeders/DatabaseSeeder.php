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
        | CATEGORY
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
        | PROVIDERS (INCLUDING YOUR LOCAL ONE)
        |--------------------------------------------------------------------------
        */
        $providers = [
            // PRETORIA
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

            // 🔥 YOUR LOCAL PROVIDER (PALMRIDGE)
            [
                'name' => 'Sizwe Khumalo',
                'email' => 'sizwe@example.com',
                'business' => 'Sizwe Local Plumbing',
                'lat' => -26.3420,
                'lng' => 28.1660,
            ],
        ];

        foreach ($providers as $provider) {

            $userId = (string) Str::uuid();
            $providerId = (string) Str::uuid();
            $serviceId = (string) Str::uuid();

            // Detect region
            $isPalmridge = $provider['lat'] < -26;

            $bio = $isPalmridge
                ? 'Reliable plumbing services in Alberton, Katlehong & Palmridge. Fast response and affordable pricing.'
                : 'Trusted plumbing services in Pretoria Gardens & Daspoort. Quality workmanship guaranteed.';

            $serviceArea = $isPalmridge
                ? 'Alberton, Katlehong, Palmridge'
                : 'Pretoria Gardens, Daspoort';

            $location = $isPalmridge
                ? 'Alberton, Katlehong, Palmridge'
                : 'Pretoria Gardens & Daspoort';

            /*
            |--------------------------------------------------------------------------
            | USER
            |--------------------------------------------------------------------------
            */
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

            /*
            |--------------------------------------------------------------------------
            | PROVIDER PROFILE
            |--------------------------------------------------------------------------
            */
            DB::table('provider_profiles')->insert([
                'provider_id' => $providerId,
                'user_id' => $userId,
                'business_name' => $provider['business'],
                'bio' => $bio,
                'years_experience' => rand(3, 12),
                'service_area' => $serviceArea,
                'kyc_status' => 'APPROVED',
                'is_online' => true,
                'service_radius_km' => 15.00,
                'last_lat' => $provider['lat'],
                'last_lng' => $provider['lng'],
                'rating_avg' => rand(40, 50) / 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | SERVICE
            |--------------------------------------------------------------------------
            */
            DB::table('services')->insert([
                'service_id' => $serviceId,
                'category_id' => $categoryId,
                'provider_id' => $providerId,
                'provider_name' => $provider['business'],
                'title' => 'Plumbing & Leak Repairs',
                'description' => 'Fix pipes, leaks, drains, blocked toilets and geysers.',
                'base_price' => rand(300, 600),
                'min_duration' => 60,
                'location' => $location,
                'rating' => rand(40, 50) / 10,
                'reviews_count' => rand(10, 50),
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

        /*
        |--------------------------------------------------------------------------
        | FETCH DATA
        |--------------------------------------------------------------------------
        */
        $users = DB::table('users')->get();
        $providers = DB::table('provider_profiles')->get();
        $services = DB::table('services')->get();

        /*
        |--------------------------------------------------------------------------
        | ADDRESSES (NOW ALSO REALISTIC)
        |--------------------------------------------------------------------------
        */
        foreach ($users as $user) {

            $isPalmridgeUser = rand(0,1);

            DB::table('addresses')->insert([
                'address_id' => (string) Str::uuid(),
                'user_id' => $user->user_id,
                'type' => 'home',
                'street' => $isPalmridgeUser
                    ? rand(8000, 9999) . ' Sadi Street'
                    : rand(10, 999) . ' Main Street',
                'city' => $isPalmridgeUser ? 'Alberton' : 'Pretoria',
                'province' => 'Gauteng',
                'postal_code' => '0001',
                'country' => 'south_africa',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}