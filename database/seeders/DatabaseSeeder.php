<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    protected $faker;
    protected $customers = [];
    protected $providers = [];
    protected $admin = [];
    protected $categories = [];
    protected $services = [];
    protected $addresses = [];
    protected $bookings = []; // service_requests
    protected $conversations = [];

    public function run()
    {
        // Disable foreign key checks for truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Truncate all tables (order matters to avoid FK constraints)
        $tables = [
            'messages',
            'conversations',
            'service_reviews',
            'service_requests',
            'bookings', // the simpler bookings table
            'payouts',
            'services',
            'provider_profiles',
            'user_dashboard_summaries',
            'settings',
            'recovery_contacts',
            'emergency_contacts',
            'locations',
            'addresses',
            'users_profile',
            'users',
            'categories',
        ];
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->faker = Faker::create('en_ZA'); // South African localisation

        // Seed in logical order
        $this->seedCategories();
        $this->seedUsers();
        $this->seedProviderProfiles();
        $this->seedServices();
        $this->seedAddressesAndRelated();
        $this->seedServiceRequests();
        $this->seedSimpleBookings(); // separate, standalone bookings
        $this->seedServiceReviews();
        $this->seedConversationsAndMessages();
        $this->seedPayouts();
        $this->seedDashboardSummaries();

        $this->command->info('Database seeded with realistic South African data!');
    }

    /**
     * Create service categories.
     */
    protected function seedCategories()
    {
        $categoriesData = [
            ['Plumbing', 'plumbing', 'fa-wrench', 'Expert plumbing services for leaks, installations, and repairs.'],
            ['Electrical', 'electrical', 'fa-bolt', 'Certified electricians for wiring, lighting, and fault finding.'],
            ['Cleaning', 'cleaning', 'fa-broom', 'Professional home and office cleaning.'],
            ['Gardening', 'gardening', 'fa-leaf', 'Garden maintenance, landscaping, and lawn care.'],
            ['Painting', 'painting', 'fa-paint-brush', 'Interior and exterior painting services.'],
            ['Carpentry', 'carpentry', 'fa-hammer', 'Custom furniture, repairs, and woodwork.'],
            ['Pool Service', 'pool-service', 'fa-swimmer', 'Pool cleaning, chemical balancing, and maintenance.'],
            ['Appliance Repair', 'appliance-repair', 'fa-tools', 'Fix fridges, washing machines, and other appliances.'],
            ['Moving', 'moving', 'fa-truck', 'Local moving and transport services.'],
            ['Security', 'security', 'fa-shield-alt', 'Alarm installation, CCTV, and security gates.'],
        ];

        foreach ($categoriesData as $cat) {
            $id = (string) Str::uuid();
            DB::table('categories')->insert([
                'id' => $id,
                'name' => $cat[0],
                'slug' => $cat[1],
                'icon' => $cat[2],
                'description' => $cat[3],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->categories[$cat[0]] = $id;
        }
    }

    /**
     * Create users (customers, providers, admin).
     */
    protected function seedUsers()
    {
        // Admin
        $adminId = (string) Str::uuid();
        DB::table('users')->insert([
            'user_id' => $adminId,
            'full_name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '+27 11 123 4567',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users_profile')->insert([
            'user_id' => $adminId,
            'full_name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '+27 11 123 4567',
            'password' => Hash::make('password'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'member_id' => 'ADMIN001',
            'role' => 'ADMIN',
            'account_status' => 'ACTIVE',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'last_login_at' => now(),
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->admin = $adminId;

        // Providers (10)
        for ($i = 1; $i <= 10; $i++) {
            $userId = (string) Str::uuid();
            $fullName = $this->faker->name;
            $email = $this->faker->unique()->safeEmail;
            $phone = $this->faker->phoneNumber;

            DB::table('users')->insert([
                'user_id' => $userId,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'provider',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users_profile')->insert([
                'user_id' => $userId,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make('password'),
                'gender' => $this->faker->randomElement(['male', 'female', 'other']),
                'member_id' => 'PRV' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'role' => 'PROVIDER',
                'account_status' => 'ACTIVE',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'last_login_at' => $this->faker->dateTimeThisMonth(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->providers[] = $userId;
        }

        // Customers (20)
        for ($i = 1; $i <= 20; $i++) {
            $userId = (string) Str::uuid();
            $fullName = $this->faker->name;
            $email = $this->faker->unique()->safeEmail;
            $phone = $this->faker->phoneNumber;

            DB::table('users')->insert([
                'user_id' => $userId,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users_profile')->insert([
                'user_id' => $userId,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make('password'),
                'gender' => $this->faker->randomElement(['male', 'female', 'other']),
                'member_id' => 'CUS' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'role' => 'CUSTOMER',
                'account_status' => 'ACTIVE',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'last_login_at' => $this->faker->dateTimeThisMonth(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->customers[] = $userId;
        }
    }

    /**
     * Create provider profiles for all provider users.
     */
    protected function seedProviderProfiles()
    {
        $provinces = ['Gauteng', 'Western Cape', 'KwaZulu-Natal', 'Eastern Cape', 'Free State', 'Mpumalanga', 'Limpopo', 'North West', 'Northern Cape'];
        $cities = [
            'Gauteng' => ['Johannesburg', 'Pretoria', 'Soweto', 'Centurion'],
            'Western Cape' => ['Cape Town', 'Stellenbosch', 'Paarl', 'George'],
            'KwaZulu-Natal' => ['Durban', 'Pietermaritzburg', 'Richards Bay', 'Newcastle'],
            'Eastern Cape' => ['Port Elizabeth', 'East London', 'Mthatha', 'Grahamstown'],
            'Free State' => ['Bloemfontein', 'Welkom', 'Bethlehem'],
            'Mpumalanga' => ['Nelspruit', 'Witbank', 'Secunda'],
            'Limpopo' => ['Polokwane', 'Tzaneen', 'Louis Trichardt'],
            'North West' => ['Rustenburg', 'Mahikeng', 'Klerksdorp'],
            'Northern Cape' => ['Kimberley', 'Upington', 'Springbok'],
        ];

        foreach ($this->providers as $userId) {
            $providerId = (string) Str::uuid();
            $province = $this->faker->randomElement($provinces);
            $city = $this->faker->randomElement($cities[$province]);
            $businessName = $this->faker->company . ' ' . $this->faker->randomElement(['Services', 'Solutions', 'Care', 'Pros']);

            DB::table('provider_profiles')->insert([
                'provider_id' => $providerId,
                'user_id' => $userId,
                'business_name' => $businessName,
                'bio' => $this->faker->paragraph(3),
                'years_experience' => $this->faker->numberBetween(1, 20),
                'service_area' => $city . ', ' . $province,
                'kyc_status' => $this->faker->randomElement(['PENDING', 'APPROVED', 'APPROVED', 'APPROVED']), // mostly approved
                'is_online' => $this->faker->boolean(70),
                'service_radius_km' => $this->faker->randomFloat(2, 5, 50),
                'last_lat' => $this->faker->latitude(-34.5, -22.5), // South Africa bounds
                'last_lng' => $this->faker->longitude(16.5, 32.9),
                'rating_avg' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Keep mapping from user_id to provider_id for later use
            $this->providerProfiles[$userId] = $providerId;
        }
    }

    /**
     * Create services for each provider.
     */
    protected function seedServices()
    {
        $categoryIds = array_values($this->categories);
        $serviceTitles = [
            'Plumbing' => ['Leak Repair', 'Geyser Installation', 'Pipe Replacement', 'Bathroom Renovation', 'Drain Cleaning'],
            'Electrical' => ['Wiring', 'Light Installation', 'Circuit Breaker Repair', 'Security Lighting', 'Generator Installation'],
            'Cleaning' => ['Deep Cleaning', 'Move-in/Move-out Cleaning', 'Carpet Cleaning', 'Window Cleaning', 'Office Cleaning'],
            'Gardening' => ['Lawn Mowing', 'Tree Trimming', 'Garden Design', 'Weed Control', 'Irrigation Installation'],
            'Painting' => ['Interior Painting', 'Exterior Painting', 'Wallpaper Removal', 'Deck Staining', 'Spray Painting'],
            'Carpentry' => ['Custom Furniture', 'Cabinet Installation', 'Door Repair', 'Deck Building', 'Shelving'],
            'Pool Service' => ['Pool Cleaning', 'Pump Repair', 'Chemical Balancing', 'Pool Cover Installation', 'Leak Detection'],
            'Appliance Repair' => ['Fridge Repair', 'Washing Machine Fix', 'Oven Repair', 'Dishwasher Service', 'Aircon Servicing'],
            'Moving' => ['Local Moving', 'Packing Service', 'Furniture Assembly', 'Storage', 'Office Relocation'],
            'Security' => ['Alarm Installation', 'CCTV Setup', 'Gate Motor Repair', 'Access Control', 'Security Assessment'],
        ];

        foreach ($this->providers as $userId) {
            $providerId = $this->providerProfiles[$userId];
            $providerName = DB::table('users')->where('user_id', $userId)->value('full_name');
            $numServices = $this->faker->numberBetween(2, 5);

            for ($s = 0; $s < $numServices; $s++) {
                $categoryId = $this->faker->randomElement($categoryIds);
                $categoryName = array_search($categoryId, $this->categories);
                $title = $this->faker->randomElement($serviceTitles[$categoryName] ?? ['General Service']);
                $price = $this->faker->randomFloat(2, 150, 800);
                $location = $this->faker->city . ', ' . $this->faker->randomElement(['Gauteng', 'Western Cape', 'KwaZulu-Natal']);

                $serviceId = (string) Str::uuid();
                DB::table('services')->insert([
                    'service_id' => $serviceId,
                    'category_id' => $categoryId,
                    'provider_id' => $providerId,
                    'provider_name' => $providerName,
                    'title' => $title,
                    'description' => $this->faker->paragraph(2),
                    'base_price' => $price,
                    'min_duration' => $this->faker->numberBetween(30, 180),
                    'location' => $location,
                    'rating' => 0,
                    'reviews_count' => 0,
                    'image' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->services[] = $serviceId;
            }
        }
    }

    /**
     * Create addresses, locations, emergency contacts, recovery contacts, and settings for each user.
     */
    protected function seedAddressesAndRelated()
    {
        $allUsers = array_merge($this->customers, $this->providers, [$this->admin]);
        $provinces = ['Gauteng', 'Western Cape', 'KwaZulu-Natal', 'Eastern Cape', 'Free State', 'Mpumalanga', 'Limpopo', 'North West', 'Northern Cape'];

        foreach ($allUsers as $userId) {
            // Addresses (1-3 per user)
            $numAddresses = $this->faker->numberBetween(1, 3);
            $defaultSet = false;
            for ($a = 0; $a < $numAddresses; $a++) {
                $province = $this->faker->randomElement($provinces);
                $city = $this->faker->city;
                $addressId = (string) Str::uuid();
                $isDefault = (!$defaultSet && ($a == 0 || $this->faker->boolean(30))) ? true : false;
                if ($isDefault) $defaultSet = true;

                DB::table('addresses')->insert([
                    'address_id' => $addressId,
                    'user_id' => $userId,
                    'type' => $this->faker->randomElement(['home', 'work', 'billing', 'shipping', 'other']),
                    'street' => $this->faker->streetAddress,
                    'city' => $city,
                    'province' => $province,
                    'postal_code' => $this->faker->postcode,
                    'country' => 'South Africa',
                    'is_default' => $isDefault,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->addresses[$userId][] = $addressId;
            }

            // Locations (saved places)
            $numLocations = $this->faker->numberBetween(0, 2);
            for ($l = 0; $l < $numLocations; $l++) {
                DB::table('locations')->insert([
                    'location_id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'name' => $this->faker->randomElement(['Home', 'Work', 'Gym', 'School', 'Parents']),
                    'address' => $this->faker->address,
                    'type' => $this->faker->randomElement(['home', 'work', 'other']),
                    'is_default' => ($l == 0 && !$defaultSet) ? true : false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Emergency contact
            DB::table('emergency_contacts')->insert([
                'emergency_contact_id' => (string) Str::uuid(),
                'user_id' => $userId,
                'name' => $this->faker->name,
                'phone' => $this->faker->phoneNumber,
                'relationship' => $this->faker->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend', 'Neighbour']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Recovery contact
            DB::table('recovery_contacts')->insert([
                'recovery_contact_id' => (string) Str::uuid(),
                'user_id' => $userId,
                'name' => $this->faker->name,
                'phone' => $this->faker->phoneNumber,
                'email' => $this->faker->safeEmail,
                'relationship' => $this->faker->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Settings
            DB::table('settings')->insert([
                'settings_id' => (string) Str::uuid(),
                'user_id' => $userId,
                'same_gender_provider' => $this->faker->boolean(20),
                'repeat_providers' => $this->faker->boolean(50),
                'auto_share' => $this->faker->boolean(10),
                'two_factor_auth' => $this->faker->boolean(30),
                'notifications' => $this->faker->boolean(90),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Create service requests (bookings) for customers.
     */
    protected function seedServiceRequests()
    {
        $statuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
        $providerUserIds = $this->providers;
        $customerUserIds = $this->customers;

        foreach ($customerUserIds as $customerId) {
            $numBookings = $this->faker->numberBetween(0, 8);
            for ($b = 0; $b < $numBookings; $b++) {
                $providerUserId = $this->faker->randomElement($providerUserIds);
                $providerId = $this->providerProfiles[$providerUserId];
                $serviceId = $this->faker->randomElement($this->services);
                $addressId = $this->faker->randomElement($this->addresses[$customerId] ?? []);
                if (!$addressId) continue; // skip if no address

                $bookingDate = $this->faker->dateTimeBetween('-3 months', '+1 month')->format('Y-m-d');
                $startTime = $this->faker->time('H:i:s');
                $endTime = date('H:i:s', strtotime($startTime) + $this->faker->numberBetween(1, 4) * 3600);
                $status = $this->faker->randomElement($statuses);
                $totalPrice = $this->faker->randomFloat(2, 200, 2000);

                $bookingId = (string) Str::uuid();
                DB::table('service_requests')->insert([
                    'booking_id' => $bookingId,
                    'user_id' => $customerId,
                    'service_id' => $serviceId,
                    'provider_id' => $providerId,
                    'address_id' => $addressId,
                    'booking_date' => $bookingDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => $status,
                    'total_price' => $totalPrice,
                    'notes' => $this->faker->optional(0.6)->sentence,
                    'address' => DB::table('addresses')->where('address_id', $addressId)->value('street') . ', ' . DB::table('addresses')->where('address_id', $addressId)->value('city'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->bookings[] = [
                    'booking_id' => $bookingId,
                    'customer_id' => $customerId,
                    'provider_id' => $providerId,
                    'service_id' => $serviceId,
                    'status' => $status,
                ];
            }
        }
    }

    /**
     * Populate the simpler 'bookings' table with independent data (to avoid confusion).
     */
    protected function seedSimpleBookings()
    {
        $statuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];

        for ($i = 0; $i < 30; $i++) {
            $userId = $this->faker->randomElement(array_merge($this->customers, $this->providers)); // can be either
            $serviceId = $this->faker->randomElement($this->services);
            $bookingDate = $this->faker->dateTimeBetween('-2 months', '+1 month')->format('Y-m-d');
            $startTime = $this->faker->time('H:i:s');
            $status = $this->faker->randomElement($statuses);
            $totalPrice = $this->faker->randomFloat(2, 100, 1500);

            DB::table('bookings')->insert([
                'id' => (string) Str::uuid(),
                'user_id' => $userId,
                'service_id' => $serviceId,
                'booking_date' => $bookingDate,
                'start_time' => $startTime,
                'status' => $status,
                'total_price' => $totalPrice,
                'notes' => $this->faker->optional(0.5)->sentence,
                'address' => $this->faker->address,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Create reviews for completed bookings (service_requests).
     * Ensures one review per customer-provider pair.
     */
    protected function seedServiceReviews()
{
    $reviewedPairs = [];

    foreach ($this->bookings as $booking) {
        if ($booking['status'] === 'completed') {

            $customerId = $booking['customer_id'];
            $providerProfileId = $booking['provider_id']; // this is provider_profiles.provider_id
            $serviceId = $booking['service_id'];

            // Convert provider_profile -> user_id
            $providerUserId = DB::table('provider_profiles')
                ->where('provider_id', $providerProfileId)
                ->value('user_id');

            if (!$providerUserId) {
                continue; // safety check
            }

            $pairKey = $customerId . '|' . $providerUserId;
            if (in_array($pairKey, $reviewedPairs)) {
                continue;
            }

            DB::table('service_reviews')->insert([
                'review_id' => (string) Str::uuid(),
                'service_id' => $serviceId,
                'to_user_id' => $providerUserId, // correct FK
                'from_user_id' => $customerId,
                'rating' => $this->faker->numberBetween(3,5),
                'comment' => $this->faker->optional(0.8)->paragraph,
                'created_at' => $this->faker->dateTimeBetween('-2 months','now'),
                'updated_at' => now(),
            ]);

            $reviewedPairs[] = $pairKey;
        }
    }

    // extra random reviews
    for ($i = 0; $i < 10; $i++) {

        $customerId = $this->faker->randomElement($this->customers);
        $providerUserId = $this->faker->randomElement($this->providers);

        $pairKey = $customerId . '|' . $providerUserId;
        if (in_array($pairKey, $reviewedPairs)) continue;

        $serviceId = $this->faker->randomElement($this->services);

        DB::table('service_reviews')->insert([
            'review_id' => (string) Str::uuid(),
            'service_id' => $serviceId,
            'to_user_id' => $providerUserId,
            'from_user_id' => $customerId,
            'rating' => $this->faker->numberBetween(1,5),
            'comment' => $this->faker->paragraph,
            'created_at' => $this->faker->dateTimeBetween('-2 months','now'),
            'updated_at' => now(),
        ]);

        $reviewedPairs[] = $pairKey;
    }
}

    /**
     * Create conversations and messages between customers and providers.
     * One conversation per customer–provider pair that has at least one booking or randomly.
     */
    protected function seedConversationsAndMessages()
    {
        $pairs = [];

        // Create conversations for each unique customer–provider from bookings
        foreach ($this->bookings as $booking) {
            $customerId = $booking['customer_id'];
            $providerId = $booking['provider_id'];
            $pairKey = $customerId . '|' . $providerId;

            if (!in_array($pairKey, $pairs)) {
                $conversationId = (string) Str::uuid();
                DB::table('conversations')->insert([
                    'conversation_id' => $conversationId,
                    'user_id' => $customerId,
                    'provider_id' => $providerId,
                    'last_message_time' => $this->faker->dateTimeBetween('-1 month', 'now'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create a few messages in this conversation
                $numMessages = $this->faker->numberBetween(1, 8);
                for ($m = 0; $m < $numMessages; $m++) {
                    $senderId = $this->faker->randomElement([$customerId, $providerId]);
                    $senderType = ($senderId == $customerId) ? 'customer' : 'provider';
                    DB::table('messages')->insert([
                        'message_id' => (string) Str::uuid(),
                        'conversation_id' => $conversationId,
                        'sender_id' => $senderId,
                        'sender_type' => $senderType,
                        'message' => $this->faker->sentence,
                        'is_read' => $this->faker->boolean(70),
                        'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
                        'updated_at' => now(),
                    ]);
                }

                $pairs[] = $pairKey;
                $this->conversations[] = $conversationId;
            }
        }

        // Create additional random conversations
        for ($i = 0; $i < 5; $i++) {
            $customerId = $this->faker->randomElement($this->customers);
            $providerUserId = $this->faker->randomElement($this->providers);
$providerId = $this->providerProfiles[$providerUserId];
            $pairKey = $customerId . '|' . $providerId;
            if (in_array($pairKey, $pairs)) continue;

            $conversationId = (string) Str::uuid();
            DB::table('conversations')->insert([
                'conversation_id' => $conversationId,
                'user_id' => $customerId,
                'provider_id' => $providerId,
                'last_message_time' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Maybe no messages yet
            if ($this->faker->boolean(70)) {
                DB::table('messages')->insert([
                    'message_id' => (string) Str::uuid(),
                    'conversation_id' => $conversationId,
                    'sender_id' => $customerId,
                    'sender_type' => 'customer',
                    'message' => $this->faker->sentence,
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $pairs[] = $pairKey;
        }
    }

    /**
     * Create payouts for providers based on completed bookings.
     */
protected function seedPayouts()
{
    $providerEarnings = [];

    foreach ($this->bookings as $booking) {

    if ($booking['status'] === 'completed') {

        $providerUserId = DB::table('provider_profiles')
            ->where('provider_id', $booking['provider_id'])
            ->value('user_id');

        $price = DB::table('service_requests')
            ->where('booking_id', $booking['booking_id'])
            ->value('total_price') ?? 0;

        if (!isset($providerEarnings[$providerUserId])) {
            $providerEarnings[$providerUserId] = 0;
        }

        $providerEarnings[$providerUserId] += $price;
    }
}

    foreach ($providerEarnings as $providerId => $total) {

        $numPayouts = ceil($total / 2000);

        for ($p = 0; $p < $numPayouts; $p++) {

            $amount = min(2000, $total - ($p * 2000));
            if ($amount <= 0) continue;

            $status = $this->faker->randomElement(['SCHEDULED', 'PAID', 'FAILED']);

            $paidAt = ($status === 'PAID')
                ? $this->faker->dateTimeBetween('-1 month', 'now')
                : null;

            $scheduledAt = ($status === 'SCHEDULED')
                ? $this->faker->dateTimeBetween('now', '+1 month')
                : null;

            DB::table('payouts')->insert([
                'payout_id' => (string) Str::uuid(),
                'provider_id' => $providerId,
                'amount' => $amount,
                'currency' => 'ZAR',
                'status' => $status,
                'scheduled_at' => $scheduledAt,
                'paid_at' => $paidAt,
                'reference' => 'PO-' . strtoupper(Str::random(8)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

    /**
     * Create dashboard summaries for all users (customers and providers).
     */
    protected function seedDashboardSummaries()
    {
        $allUsers = array_merge($this->customers, $this->providers);

        foreach ($allUsers as $userId) {
            $isProvider = in_array($userId, $this->providers);

            // Count bookings
            if ($isProvider) {
                $providerId = $this->providerProfiles[$userId];
                $bookingsOffered = DB::table('service_requests')->where('provider_id', $providerId)->count();
                $bookingsAccepted = DB::table('service_requests')->where('provider_id', $providerId)->whereIn('status', ['confirmed', 'in_progress', 'completed'])->count();
                $bookingsInProgress = DB::table('service_requests')->where('provider_id', $providerId)->where('status', 'in_progress')->count();
                $bookingsCompleted = DB::table('service_requests')->where('provider_id', $providerId)->where('status', 'completed')->count();

                // Average rating from service_reviews to_user_id = userId
                $avgRating = DB::table('service_reviews')->where('to_user_id', $userId)->avg('rating') ?? 0;
            } else {
                $bookingsRequested = DB::table('service_requests')->where('user_id', $userId)->count();
                $bookingsAccepted = DB::table('service_requests')->where('user_id', $userId)->whereIn('status', ['confirmed', 'in_progress', 'completed'])->count();
                $bookingsInProgress = DB::table('service_requests')->where('user_id', $userId)->where('status', 'in_progress')->count();
                $bookingsOffered = 0;
                $avgRating = 0; // customers don't have a rating
            }

            // Unread messages: count messages in conversations where user is participant and is_read = false
            $unreadMessages = DB::table('messages')
                ->join('conversations', 'messages.conversation_id', '=', 'conversations.conversation_id')
                ->where(function ($q) use ($userId) {
                    $q->where('conversations.user_id', $userId)
                      ->orWhere('conversations.provider_id', $userId);
                })
                ->where('messages.is_read', false)
                ->where('messages.sender_id', '!=', $userId)
                ->count();

            // Last activity: max of updated_at from related tables (simplified: use users.updated_at)
            $lastActivity = DB::table('users')->where('user_id', $userId)->value('updated_at');

            DB::table('user_dashboard_summaries')->insert([
                'user_id' => $userId,
                'name' => DB::table('users')->where('user_id', $userId)->value('full_name') . ' Summary',
                'bookings_requested' => $bookingsRequested ?? 0,
                'bookings_offered' => $bookingsOffered,
                'bookings_accepted' => $bookingsAccepted ?? 0,
                'bookings_in_progress' => $bookingsInProgress ?? 0,
                'unread_messages' => $unreadMessages,
                'average_rating' => $avgRating,
                'last_activity_at' => $lastActivity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}