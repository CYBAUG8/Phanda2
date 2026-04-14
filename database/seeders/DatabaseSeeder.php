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

        /*
|--------------------------------------------------------------------------
| FETCH USERS & SERVICES
|--------------------------------------------------------------------------
*/
$users = DB::table('users')->get();
$providers = DB::table('provider_profiles')->get();
$services = DB::table('services')->get();

/*
|--------------------------------------------------------------------------
| ADDRESSES + SETTINGS + CONTACTS
|--------------------------------------------------------------------------
*/
foreach ($users as $user) {

    $addressId = (string) Str::uuid();

    // ADDRESS
    DB::table('addresses')->insert([
        'address_id' => $addressId,
        'user_id' => $user->user_id,
        'type' => 'home',
        'street' => rand(10, 999) . ' Main Street',
        'city' => 'Pretoria',
        'province' => 'Gauteng',
        'postal_code' => '0001',
        'country' => 'south_africa',
        'is_default' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // SETTINGS
    DB::table('settings')->insert([
        'settings_id' => (string) Str::uuid(),
        'user_id' => $user->user_id,
        'same_gender_provider' => rand(0,1),
        'repeat_providers' => rand(0,1),
        'auto_share' => rand(0,1),
        'two_factor_auth' => rand(0,1),
        'notifications' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // EMERGENCY CONTACT
    DB::table('emergency_contacts')->insert([
        'emergency_contact_id' => (string) Str::uuid(),
        'user_id' => $user->user_id,
        'name' => 'Emergency Contact',
        'phone' => '+27715555555',
        'relationship' => 'Sibling',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // RECOVERY CONTACT (1:1)
    DB::table('recovery_contacts')->insert([
        'recovery_contact_id' => (string) Str::uuid(),
        'user_id' => $user->user_id,
        'name' => 'Recovery Person',
        'phone' => '+27716666666',
        'email' => 'recovery@example.com',
        'relationship' => 'Friend',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // LOCATION
    DB::table('locations')->insert([
        'location_id' => (string) Str::uuid(),
        'user_id' => $user->user_id,
        'name' => 'Home',
        'address' => 'Pretoria Gardens',
        'type' => 'home',
        'is_default' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // LOGIN HISTORY
    DB::table('login_histories')->insert([
        'login_history_id' => (string) Str::uuid(),
        'user_id' => $user->user_id,
        'login_at' => now()->subDays(rand(0,10)),
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Chrome',
        'device' => 'Desktop',
        'location' => 'Pretoria',
        'status' => 'success',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // DASHBOARD SUMMARY
    DB::table('user_dashboard_summaries')->insert([
        'user_id' => $user->user_id,
        'name' => $user->full_name,
        'bookings_requested' => rand(0,5),
        'bookings_offered' => rand(0,5),
        'bookings_accepted' => rand(0,5),
        'bookings_in_progress' => rand(0,3),
        'unread_messages' => rand(0,5),
        'average_rating' => rand(30,50)/10,
        'last_activity_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/*
|--------------------------------------------------------------------------
| BOOKINGS + REVIEWS
|--------------------------------------------------------------------------
*/
foreach ($users->where('role', 'customer') as $customer) {

    $service = $services->random();
    $provider = $providers->where('provider_id', $service->provider_id)->first();

    $address = DB::table('addresses')
        ->where('user_id', $customer->user_id)
        ->first();

    $bookingId = (string) Str::uuid();

    // BOOKING
    DB::table('bookings')->insert([
        'id' => $bookingId,
        'user_id' => $customer->user_id,
        'service_id' => $service->service_id,
        'booking_date' => now()->addDays(rand(1,5)),
        'start_time' => '10:00:00',
        'status' => 'confirmed',
        'total_price' => $service->base_price,
        'notes' => 'Fix leaking pipe',
        'address' => $address->street,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // REVIEW
    DB::table('service_reviews')->insert([
        'review_id' => (string) Str::uuid(),
        'service_id' => $service->service_id,
        'to_user_id' => $provider->user_id,
        'from_user_id' => $customer->user_id,
        'rating' => rand(3,5),
        'comment' => 'Great service!',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/*
|--------------------------------------------------------------------------
| CHAT SYSTEM
|--------------------------------------------------------------------------
*/
foreach ($providers as $provider) {

    $customer = $users->where('role', 'customer')->random();

    $conversationId = (string) Str::uuid();

    // CONVERSATION
    DB::table('conversations')->insert([
        'conversation_id' => $conversationId,
        'user_id' => $customer->user_id,
        'provider_id' => $provider->provider_id,
        'last_message_time' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // MESSAGES
    DB::table('messages')->insert([
        'message_id' => (string) Str::uuid(),
        'conversation_id' => $conversationId,
        'sender_id' => $customer->user_id,
        'sender_type' => 'user',
        'message' => 'Hi, I need help with plumbing.',
        'is_read' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('messages')->insert([
        'message_id' => (string) Str::uuid(),
        'conversation_id' => $conversationId,
        'sender_id' => $provider->provider_id,
        'sender_type' => 'provider',
        'message' => 'Sure, I can assist you.',
        'is_read' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/*
|--------------------------------------------------------------------------
| PAYOUTS
|--------------------------------------------------------------------------
*/
foreach ($providers as $provider) {

    DB::table('payouts')->insert([
        'payout_id' => (string) Str::uuid(),
        'provider_id' => $provider->provider_id,
        'amount' => rand(500,2000),
        'currency' => 'ZAR',
        'status' => 'PAID',
        'scheduled_at' => now()->subDays(2),
        'paid_at' => now()->subDay(),
        'reference' => 'PAY-' . rand(1000,9999),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/*
|--------------------------------------------------------------------------
| SERVICE REQUESTS (Bookings v2)
|--------------------------------------------------------------------------
*/
$customers = DB::table('users')->where('role', 'customer')->get();
$services = DB::table('services')->get();
$providers = DB::table('provider_profiles')->get();

foreach ($customers as $customer) {

    // pick a random service
    $service = $services->random();

    // get matching provider from service
    $provider = $providers->where('provider_id', $service->provider_id)->first();

    // get user's address
    $address = DB::table('addresses')
        ->where('user_id', $customer->user_id)
        ->inRandomOrder()
        ->first();

    // safety check (prevents null FK errors)
    if (!$provider || !$address) {
        continue;
    }

    $startTime = now()->addDays(rand(1, 5))->setHour(rand(8, 16))->setMinute(0);
    $endTime = (clone $startTime)->addHour(2);

    DB::table('service_requests')->insert([
        'booking_id' => (string) Str::uuid(),

        'user_id' => $customer->user_id,
        'service_id' => $service->service_id,
        'provider_id' => $provider->provider_id,
        'address_id' => $address->address_id,

        'booking_date' => $startTime->toDateString(),
        'start_time' => $startTime->format('H:i:s'),
        'end_time' => $endTime->format('H:i:s'),

        'status' => collect(['pending', 'confirmed', 'in_progress', 'completed'])->random(),

        'total_price' => $service->base_price + rand(50, 200),
        'notes' => 'Customer requested urgent plumbing assistance',
        'address' => $address->street . ', ' . $address->city,

        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
    }
}
