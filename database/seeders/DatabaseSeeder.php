<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
<<<<<<< HEAD

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
=======
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
    protected $bookings = [];
    protected $conversations = [];
    protected $providerProfiles = [];
>>>>>>> feature2

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

<<<<<<< HEAD
        foreach ($providers as $provider) {
=======
        $providerMessages = [
            'Hello! Yes, I am available on Friday afternoon.',
            'The job should take about two to three hours.',
            'I will bring all necessary tools and materials.',
            'My rate for that service is R450 including materials.',
            'Confirmed, I will see you at 10am.',
            'I am on my way, should be there in about 20 minutes.',
            'Thank you for the booking, looking forward to it.',
            'Please make sure the area is accessible when I arrive.',
            'I can do Saturday at 9am if that works for you.',
            'Payment can be made by EFT or cash on the day.',
            'I have completed the job, please let me know if you are satisfied.',
            'Happy to help! Feel free to book again anytime.',
        ];

        return $senderType === 'customer'
            ? $this->faker->randomElement($customerMessages)
            : $this->faker->randomElement($providerMessages);
    }

    /**
     * Realistic English review comments.
     */
    protected function realisticReview(): string
    {
        $reviews = [
            'Excellent service! The job was done quickly and professionally.',
            'Very happy with the work. Will definitely use this provider again.',
            'Arrived on time and completed everything as promised. Highly recommended.',
            'Good quality work at a fair price. No complaints at all.',
            'The provider was friendly, efficient, and left the place clean.',
            'Fantastic experience from start to finish. Five stars well deserved.',
            'Solid work, very knowledgeable and explained everything clearly.',
            'Reasonable pricing and great results. Would recommend to friends.',
            'Did a thorough job and finished ahead of schedule. Very impressed.',
            'Professional attitude and high quality finish. Will book again.',
            'The work was okay but took a bit longer than expected.',
            'Decent service, communication could be better but the job was done.',
            'Average experience. The result was acceptable but not outstanding.',
            'Not bad overall, a few minor issues but nothing serious.',
        ];

        return $this->faker->randomElement($reviews);
    }

    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'messages',
            'conversations',
            'service_reviews',
            'service_requests',
            'bookings',
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

        // Force English locale to avoid foreign language faker output
        $this->faker = Faker::create('en_ZA');

        $this->seedCategories();
        $this->seedUsers();
        $this->seedProviderProfiles();
        $this->call(ServiceSeeder::class);
        $this->services = DB::table('services')->pluck('service_id')->all();
        $this->seedAddressesAndRelated();
        $this->seedServiceRequests();
        $this->seedSimpleBookings();
        $this->seedServiceReviews();
        $this->seedConversationsAndMessages();
        $this->seedPayouts();
        $this->seedDashboardSummaries();

        $this->command->info('Database seeded with realistic South African data!');
    }

    protected function seedCategories()
    {
        $categoriesData = [
            ['Plumbing',        'plumbing',        'fa-wrench',      'Expert plumbing services for leaks, installations, and repairs.'],
            ['Electrical',      'electrical',      'fa-bolt',        'Certified electricians for wiring, lighting, and fault finding.'],
            ['Cleaning',        'cleaning',        'fa-broom',       'Professional home and office cleaning.'],
            ['Gardening',       'gardening',       'fa-leaf',        'Garden maintenance, landscaping, and lawn care.'],
            ['Painting',        'painting',        'fa-paint-brush', 'Interior and exterior painting services.'],
            ['Carpentry',       'carpentry',       'fa-hammer',      'Custom furniture, repairs, and woodwork.'],
            ['Pool Service',    'pool-service',    'fa-swimmer',     'Pool cleaning, chemical balancing, and maintenance.'],
            ['Appliance Repair','appliance-repair','fa-tools',       'Fix fridges, washing machines, and other appliances.'],
            ['Moving',          'moving',          'fa-truck',       'Local moving and transport services.'],
            ['Security',        'security',        'fa-shield-alt',  'Alarm installation, CCTV, and security gates.'],
        ];

        foreach ($categoriesData as $cat) {
            $id = (string) Str::uuid();
            DB::table('categories')->insert([
                'id'          => $id,
                'name'        => $cat[0],
                'slug'        => $cat[1],
                'icon'        => $cat[2],
                'description' => $cat[3],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $this->categories[$cat[0]] = $id;
        }
    }

    protected function seedUsers()
    {
        // South African first and last names
        $saFirstNames = [
            'Thabo', 'Sipho', 'Lerato', 'Nomsa', 'Bongani', 'Zanele', 'Lungelo', 'Ayanda',
            'Nkosi', 'Thandi', 'Siyanda', 'Lindiwe', 'Mandla', 'Nokwanda', 'Sifiso',
            'Palesa', 'Lungisa', 'Nomvula', 'Thabiso', 'Nandi', 'Kagiso', 'Refiloe',
            'Mpho', 'Khanyisile', 'Lebo', 'Sbusiso', 'Yolanda', 'Themba', 'Nolwazi', 'Zinhle',
        ];
        $saLastNames = [
            'Ndlovu', 'Dlamini', 'Nkosi', 'Mthembu', 'Zulu', 'Khumalo', 'Mokoena',
            'Sithole', 'Mahlangu', 'Molefe', 'Nkululeko', 'Bhengu', 'Cele', 'Shabalala',
            'Mkhize', 'Ntanzi', 'Vilakazi', 'Radebe', 'Ntuli', 'Luthuli',
        ];

        $usedEmails = [];

        $generateUser = function (string $role, string $memberId) use ($saFirstNames, $saLastNames, &$usedEmails): array {
            $firstName = $this->faker->randomElement($saFirstNames);
            $lastName  = $this->faker->randomElement($saLastNames);
            $fullName  = $firstName . ' ' . $lastName;

            // Generate a name-matching unique email
            do {
                $email = $this->emailFromName($fullName);
            } while (in_array($email, $usedEmails));
            $usedEmails[] = $email;

            $phone = '+27 ' . $this->faker->numerify('## ### ####');

            return compact('fullName', 'email', 'phone');
        };

        // Admin
        $adminId  = (string) Str::uuid();
        $adminEmail = 'admin@phanda.co.za';
        DB::table('users')->insert([
            'user_id'           => $adminId,
            'full_name'         => 'Admin User',
            'email'             => $adminEmail,
            'phone'             => '+27 11 123 4567',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
        DB::table('users_profile')->insert([
            'user_id'           => $adminId,
            'full_name'         => 'Admin User',
            'email'             => $adminEmail,
            'phone'             => '+27 11 123 4567',
            'password'          => Hash::make('password'),
            'gender'            => 'other',
            'member_id'         => 'ADMIN001',
            'role'              => 'ADMIN',
            'account_status'    => 'ACTIVE',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'last_login_at'     => now(),
            'remember_token'    => Str::random(10),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
        $this->admin = $adminId;

        // Providers (10)
        for ($i = 1; $i <= 10; $i++) {
            $userId   = (string) Str::uuid();
            $data     = $generateUser('provider', 'PRV' . str_pad($i, 3, '0', STR_PAD_LEFT));

            DB::table('users')->insert([
                'user_id'           => $userId,
                'full_name'         => $data['fullName'],
                'email'             => $data['email'],
                'phone'             => $data['phone'],
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'role'              => 'provider',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            DB::table('users_profile')->insert([
                'user_id'           => $userId,
                'full_name'         => $data['fullName'],
                'email'             => $data['email'],
                'phone'             => $data['phone'],
                'password'          => Hash::make('password'),
                'gender'            => $this->faker->randomElement(['male', 'female']),
                'member_id'         => 'PRV' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'role'              => 'PROVIDER',
                'account_status'    => 'ACTIVE',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'last_login_at'     => $this->faker->dateTimeThisMonth(),
                'remember_token'    => Str::random(10),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $this->providers[] = $userId;
        }

        // Customers (20)
        for ($i = 1; $i <= 20; $i++) {
>>>>>>> feature2
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
