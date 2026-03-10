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
    protected $bookings = [];
    protected $conversations = [];
    protected $providerProfiles = [];

    /**
     * Generate a realistic email from a full name.
     * e.g. "Thuli Ndlovu" -> "thuli.ndlovu@gmail.com"
     */
    protected function emailFromName(string $fullName): string
    {
        $parts = explode(' ', strtolower(trim($fullName)));
        $first = preg_replace('/[^a-z]/', '', $parts[0] ?? 'user');
        $last  = preg_replace('/[^a-z]/', '', $parts[1] ?? 'user');
        $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'icloud.com', 'webmail.co.za', 'vodamail.co.za'];
        $domain = $this->faker->randomElement($domains);
        $separator = $this->faker->randomElement(['.', '_', '']);
        $suffix = $this->faker->boolean(30) ? $this->faker->numberBetween(1, 99) : '';
        return $first . $separator . $last . $suffix . '@' . $domain;
    }

    /**
     * Realistic English messages between a customer and provider.
     */
    protected function realisticMessage(string $senderType): string
    {
        $customerMessages = [
            'Hi, I would like to book your service for next week.',
            'Is Friday afternoon available?',
            'Can you give me a rough quote for the job?',
            'Great, I will confirm by tomorrow.',
            'Do you bring your own equipment?',
            'How long will the job take approximately?',
            'I have confirmed the booking, see you then.',
            'Please call me when you are on your way.',
            'Can we reschedule to Saturday morning instead?',
            'Thank you, the work was done really well!',
            'Hi, just checking if you are still coming today?',
            'What payment methods do you accept?',
        ];

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
        $this->seedServices();
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
            $userId = (string) Str::uuid();
            $data   = $generateUser('customer', 'CUS' . str_pad($i, 3, '0', STR_PAD_LEFT));

            DB::table('users')->insert([
                'user_id'           => $userId,
                'full_name'         => $data['fullName'],
                'email'             => $data['email'],
                'phone'             => $data['phone'],
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'role'              => 'customer',
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
                'member_id'         => 'CUS' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'role'              => 'CUSTOMER',
                'account_status'    => 'ACTIVE',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'last_login_at'     => $this->faker->dateTimeThisMonth(),
                'remember_token'    => Str::random(10),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $this->customers[] = $userId;
        }
    }

    protected function seedProviderProfiles()
    {
        $provinces = ['Gauteng', 'Western Cape', 'KwaZulu-Natal', 'Eastern Cape', 'Free State', 'Mpumalanga', 'Limpopo', 'North West', 'Northern Cape'];
        $cities = [
            'Gauteng'       => ['Johannesburg', 'Pretoria', 'Soweto', 'Centurion'],
            'Western Cape'  => ['Cape Town', 'Stellenbosch', 'Paarl', 'George'],
            'KwaZulu-Natal' => ['Durban', 'Pietermaritzburg', 'Richards Bay', 'Newcastle'],
            'Eastern Cape'  => ['Port Elizabeth', 'East London', 'Mthatha', 'Grahamstown'],
            'Free State'    => ['Bloemfontein', 'Welkom', 'Bethlehem'],
            'Mpumalanga'    => ['Nelspruit', 'Witbank', 'Secunda'],
            'Limpopo'       => ['Polokwane', 'Tzaneen', 'Louis Trichardt'],
            'North West'    => ['Rustenburg', 'Mahikeng', 'Klerksdorp'],
            'Northern Cape' => ['Kimberley', 'Upington', 'Springbok'],
        ];

        $businessSuffixes = ['Services', 'Solutions', 'Care', 'Pros', 'Experts', 'Works'];

        foreach ($this->providers as $userId) {
            $providerId  = (string) Str::uuid();
            $province    = $this->faker->randomElement($provinces);
            $city        = $this->faker->randomElement($cities[$province]);
            $providerName = DB::table('users')->where('user_id', $userId)->value('full_name');
            $lastName    = explode(' ', $providerName)[1] ?? $providerName;
            $businessName = $lastName . ' ' . $this->faker->randomElement($businessSuffixes);

            // English bio
            $bios = [
                'We provide reliable and professional services across the region with over a decade of experience.',
                'Our team is dedicated to delivering high-quality workmanship at competitive prices.',
                'Fully certified and insured, we pride ourselves on punctuality and customer satisfaction.',
                'We have built a strong reputation for honest, efficient, and skilled service delivery.',
                'Your satisfaction is our priority. We show up on time and get the job done right.',
            ];

            DB::table('provider_profiles')->insert([
                'provider_id'       => $providerId,
                'user_id'           => $userId,
                'business_name'     => $businessName,
                'bio'               => $this->faker->randomElement($bios),
                'years_experience'  => $this->faker->numberBetween(1, 20),
                'service_area'      => $city . ', ' . $province,
                'kyc_status'        => $this->faker->randomElement(['PENDING', 'APPROVED', 'APPROVED', 'APPROVED']),
                'is_online'         => $this->faker->boolean(70),
                'service_radius_km' => $this->faker->randomFloat(2, 5, 50),
                'last_lat'          => $this->faker->latitude(-34.5, -22.5),
                'last_lng'          => $this->faker->longitude(16.5, 32.9),
                'rating_avg'        => 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $this->providerProfiles[$userId] = $providerId;
        }
    }

    protected function seedServices()
    {
        $categoryIds = array_values($this->categories);
        $serviceTitles = [
            'Plumbing'        => ['Leak Repair', 'Geyser Installation', 'Pipe Replacement', 'Bathroom Renovation', 'Drain Cleaning'],
            'Electrical'      => ['Wiring', 'Light Installation', 'Circuit Breaker Repair', 'Security Lighting', 'Generator Installation'],
            'Cleaning'        => ['Deep Cleaning', 'Move-in/Move-out Cleaning', 'Carpet Cleaning', 'Window Cleaning', 'Office Cleaning'],
            'Gardening'       => ['Lawn Mowing', 'Tree Trimming', 'Garden Design', 'Weed Control', 'Irrigation Installation'],
            'Painting'        => ['Interior Painting', 'Exterior Painting', 'Wallpaper Removal', 'Deck Staining', 'Spray Painting'],
            'Carpentry'       => ['Custom Furniture', 'Cabinet Installation', 'Door Repair', 'Deck Building', 'Shelving'],
            'Pool Service'    => ['Pool Cleaning', 'Pump Repair', 'Chemical Balancing', 'Pool Cover Installation', 'Leak Detection'],
            'Appliance Repair'=> ['Fridge Repair', 'Washing Machine Fix', 'Oven Repair', 'Dishwasher Service', 'Aircon Servicing'],
            'Moving'          => ['Local Moving', 'Packing Service', 'Furniture Assembly', 'Storage', 'Office Relocation'],
            'Security'        => ['Alarm Installation', 'CCTV Setup', 'Gate Motor Repair', 'Access Control', 'Security Assessment'],
        ];

        $serviceDescriptions = [
            'We handle this job thoroughly and professionally, ensuring a high-quality finish every time.',
            'Our experienced team will assess the situation and complete the work to the highest standard.',
            'Fast, reliable, and affordable. We make sure the job is done right the first time.',
            'Using quality materials and proven techniques, we deliver lasting results you can count on.',
            'We take pride in our work and treat your property with care and respect throughout the job.',
        ];

        foreach ($this->providers as $userId) {
            $providerId   = $this->providerProfiles[$userId];
            $providerName = DB::table('users')->where('user_id', $userId)->value('full_name');
            $numServices  = $this->faker->numberBetween(2, 5);

            for ($s = 0; $s < $numServices; $s++) {
                $categoryId   = $this->faker->randomElement($categoryIds);
                $categoryName = array_search($categoryId, $this->categories);
                $title        = $this->faker->randomElement($serviceTitles[$categoryName] ?? ['General Service']);
                $price        = $this->faker->randomFloat(2, 150, 800);
                $province     = $this->faker->randomElement(['Gauteng', 'Western Cape', 'KwaZulu-Natal']);
                $city         = $this->faker->randomElement(['Johannesburg', 'Cape Town', 'Durban', 'Pretoria', 'Centurion']);
                $location     = $city . ', ' . $province;

                $serviceId = (string) Str::uuid();
                DB::table('services')->insert([
                    'service_id'   => $serviceId,
                    'category_id'  => $categoryId,
                    'provider_id'  => $providerId,
                    'provider_name'=> $providerName,
                    'title'        => $title,
                    'description'  => $this->faker->randomElement($serviceDescriptions),
                    'base_price'   => $price,
                    'min_duration' => $this->faker->numberBetween(30, 180),
                    'location'     => $location,
                    'rating'       => 0,
                    'reviews_count'=> 0,
                    'image'        => null,
                    'is_active'    => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                $this->services[] = $serviceId;
            }
        }
    }

    protected function seedAddressesAndRelated()
    {
        $allUsers = array_merge($this->customers, $this->providers, [$this->admin]);
        $provinces = ['Gauteng', 'Western Cape', 'KwaZulu-Natal', 'Eastern Cape', 'Free State', 'Mpumalanga', 'Limpopo', 'North West', 'Northern Cape'];
        $saStreets = ['Main Road', 'Church Street', 'Long Street', 'Commissioner Street', 'Voortrekker Road', 'Jan Smuts Avenue', 'Bree Street', 'Loop Street', 'Adderley Street', 'West Street'];
        $saCities  = ['Johannesburg', 'Cape Town', 'Durban', 'Pretoria', 'Port Elizabeth', 'Bloemfontein', 'Nelspruit', 'Polokwane', 'Kimberley', 'Rustenburg'];

        foreach ($allUsers as $userId) {
            $numAddresses = $this->faker->numberBetween(1, 3);
            $defaultSet   = false;

            for ($a = 0; $a < $numAddresses; $a++) {
                $province   = $this->faker->randomElement($provinces);
                $city       = $this->faker->randomElement($saCities);
                $streetNum  = $this->faker->numberBetween(1, 200);
                $street     = $streetNum . ' ' . $this->faker->randomElement($saStreets);
                $addressId  = (string) Str::uuid();
                $isDefault  = (!$defaultSet && ($a == 0 || $this->faker->boolean(30)));
                if ($isDefault) $defaultSet = true;

                DB::table('addresses')->insert([
                    'address_id'  => $addressId,
                    'user_id'     => $userId,
                    'type'        => $this->faker->randomElement(['home', 'work', 'billing', 'shipping', 'other']),
                    'street'      => $street,
                    'city'        => $city,
                    'province'    => $province,
                    'postal_code' => $this->faker->numerify('####'),
                    'country'     => 'South Africa',
                    'is_default'  => $isDefault,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                $this->addresses[$userId][] = $addressId;
            }

            // Locations
            $numLocations = $this->faker->numberBetween(0, 2);
            for ($l = 0; $l < $numLocations; $l++) {
                $locCity   = $this->faker->randomElement($saCities);
                $locStreet = $this->faker->numberBetween(1, 150) . ' ' . $this->faker->randomElement($saStreets);
                DB::table('locations')->insert([
                    'location_id' => (string) Str::uuid(),
                    'user_id'     => $userId,
                    'name'        => $this->faker->randomElement(['Home', 'Work', 'Gym', 'School', 'Parents']),
                    'address'     => $locStreet . ', ' . $locCity,
                    'type'        => $this->faker->randomElement(['home', 'work', 'other']),
                    'is_default'  => ($l == 0 && !$defaultSet),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            // Emergency contact
            $emergencyName = $this->faker->randomElement(['Sipho', 'Nomsa', 'Thabo', 'Lerato', 'Bongani'])
                           . ' ' . $this->faker->randomElement(['Dlamini', 'Nkosi', 'Molefe', 'Khumalo', 'Zulu']);
            DB::table('emergency_contacts')->insert([
                'emergency_contact_id' => (string) Str::uuid(),
                'user_id'              => $userId,
                'name'                 => $emergencyName,
                'phone'                => '+27 ' . $this->faker->numerify('## ### ####'),
                'relationship'         => $this->faker->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend', 'Neighbour']),
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            // Recovery contact
            $recoveryName = $this->faker->randomElement(['Zanele', 'Lungelo', 'Ayanda', 'Nkosi', 'Thabiso'])
                          . ' ' . $this->faker->randomElement(['Sithole', 'Mthembu', 'Mahlangu', 'Cele', 'Vilakazi']);
            DB::table('recovery_contacts')->insert([
                'recovery_contact_id' => (string) Str::uuid(),
                'user_id'             => $userId,
                'name'                => $recoveryName,
                'phone'               => '+27 ' . $this->faker->numerify('## ### ####'),
                'email'               => $this->emailFromName($recoveryName),
                'relationship'        => $this->faker->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend']),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            // Settings
            DB::table('settings')->insert([
                'settings_id'        => (string) Str::uuid(),
                'user_id'            => $userId,
                'same_gender_provider'=> $this->faker->boolean(20),
                'repeat_providers'   => $this->faker->boolean(50),
                'auto_share'         => $this->faker->boolean(10),
                'two_factor_auth'    => $this->faker->boolean(30),
                'notifications'      => $this->faker->boolean(90),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }

    protected function seedServiceRequests()
    {
        $statuses       = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
        $providerUserIds = $this->providers;
        $customerUserIds = $this->customers;
        $saStreets       = ['Main Road', 'Church Street', 'Long Street', 'Commissioner Street', 'Voortrekker Road'];
        $saCities        = ['Johannesburg', 'Cape Town', 'Durban', 'Pretoria', 'Port Elizabeth'];

        foreach ($customerUserIds as $customerId) {
            $numBookings = $this->faker->numberBetween(0, 8);
            for ($b = 0; $b < $numBookings; $b++) {
                $providerUserId = $this->faker->randomElement($providerUserIds);
                $providerId     = $this->providerProfiles[$providerUserId];
                $serviceId      = $this->faker->randomElement($this->services);
                $addressId      = $this->faker->randomElement($this->addresses[$customerId] ?? []);
                if (!$addressId) continue;

                $bookingDate = $this->faker->dateTimeBetween('-3 months', '+1 month')->format('Y-m-d');
                $startTime   = $this->faker->time('H:i:s');
                $endTime     = date('H:i:s', strtotime($startTime) + $this->faker->numberBetween(1, 4) * 3600);
                $status      = $this->faker->randomElement($statuses);
                $totalPrice  = $this->faker->randomFloat(2, 200, 2000);
                $streetNum   = $this->faker->numberBetween(1, 200);
                $address     = $streetNum . ' ' . $this->faker->randomElement($saStreets) . ', ' . $this->faker->randomElement($saCities);

                $bookingId = (string) Str::uuid();
                DB::table('service_requests')->insert([
                    'booking_id'  => $bookingId,
                    'user_id'     => $customerId,
                    'service_id'  => $serviceId,
                    'provider_id' => $providerId,
                    'address_id'  => $addressId,
                    'booking_date'=> $bookingDate,
                    'start_time'  => $startTime,
                    'end_time'    => $endTime,
                    'status'      => $status,
                    'total_price' => $totalPrice,
                    'notes'       => $this->faker->boolean(60)
                        ? $this->faker->randomElement([
                            'Please bring your own cleaning supplies.',
                            'The gate code is 1234.',
                            'Please call before arriving.',
                            'Dog on the property, please be cautious.',
                            'Park in the driveway.',
                            'Access via the side gate.',
                        ])
                        : null,
                    'address'     => $address,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                $this->bookings[] = [
                    'booking_id'  => $bookingId,
                    'customer_id' => $customerId,
                    'provider_id' => $providerId,
                    'service_id'  => $serviceId,
                    'status'      => $status,
                ];
            }
        }
    }

    protected function seedSimpleBookings()
    {
        $statuses    = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
        $saAddresses = ['12 Main Road, Johannesburg', '45 Church Street, Pretoria', '7 Long Street, Cape Town', '88 West Street, Durban', '3 Adderley Street, Cape Town'];

        for ($i = 0; $i < 30; $i++) {
            $userId      = $this->faker->randomElement(array_merge($this->customers, $this->providers));
            $serviceId   = $this->faker->randomElement($this->services);
            $bookingDate = $this->faker->dateTimeBetween('-2 months', '+1 month')->format('Y-m-d');
            $startTime   = $this->faker->time('H:i:s');
            $status      = $this->faker->randomElement($statuses);
            $totalPrice  = $this->faker->randomFloat(2, 100, 1500);

            DB::table('bookings')->insert([
                'id'          => (string) Str::uuid(),
                'user_id'     => $userId,
                'service_id'  => $serviceId,
                'booking_date'=> $bookingDate,
                'start_time'  => $startTime,
                'status'      => $status,
                'total_price' => $totalPrice,
                'notes'       => $this->faker->boolean(50)
                    ? $this->faker->randomElement([
                        'Please confirm 30 minutes before arrival.',
                        'Intercom at the front gate.',
                        'Second floor, no lift available.',
                        'Please use the back entrance.',
                    ])
                    : null,
                'address'     => $this->faker->randomElement($saAddresses),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    protected function seedServiceReviews()
    {
        $reviewedPairs = [];

        foreach ($this->bookings as $booking) {
            if ($booking['status'] === 'completed') {
                $customerId       = $booking['customer_id'];
                $providerProfileId = $booking['provider_id'];
                $serviceId        = $booking['service_id'];

                $providerUserId = DB::table('provider_profiles')
                    ->where('provider_id', $providerProfileId)
                    ->value('user_id');

                if (!$providerUserId) continue;

                $pairKey = $customerId . '|' . $providerUserId;
                if (in_array($pairKey, $reviewedPairs)) continue;

                DB::table('service_reviews')->insert([
                    'review_id'    => (string) Str::uuid(),
                    'service_id'   => $serviceId,
                    'to_user_id'   => $providerUserId,
                    'from_user_id' => $customerId,
                    'rating'       => $this->faker->numberBetween(3, 5),
                    'comment'      => $this->faker->boolean(80) ? $this->realisticReview() : null,
                    'created_at'   => $this->faker->dateTimeBetween('-2 months', 'now'),
                    'updated_at'   => now(),
                ]);

                $reviewedPairs[] = $pairKey;
            }
        }

        // Extra random reviews
        for ($i = 0; $i < 10; $i++) {
            $customerId     = $this->faker->randomElement($this->customers);
            $providerUserId = $this->faker->randomElement($this->providers);
            $pairKey        = $customerId . '|' . $providerUserId;
            if (in_array($pairKey, $reviewedPairs)) continue;

            $serviceId = $this->faker->randomElement($this->services);

            DB::table('service_reviews')->insert([
                'review_id'    => (string) Str::uuid(),
                'service_id'   => $serviceId,
                'to_user_id'   => $providerUserId,
                'from_user_id' => $customerId,
                'rating'       => $this->faker->numberBetween(1, 5),
                'comment'      => $this->realisticReview(),
                'created_at'   => $this->faker->dateTimeBetween('-2 months', 'now'),
                'updated_at'   => now(),
            ]);

            $reviewedPairs[] = $pairKey;
        }
    }

    protected function seedConversationsAndMessages()
    {
        $pairs = [];

        foreach ($this->bookings as $booking) {
            $customerId = $booking['customer_id'];
            $providerId = $booking['provider_id'];
            $pairKey    = $customerId . '|' . $providerId;

            if (!in_array($pairKey, $pairs)) {
                $conversationId = (string) Str::uuid();
                DB::table('conversations')->insert([
                    'conversation_id'   => $conversationId,
                    'user_id'           => $customerId,
                    'provider_id'       => $providerId,
                    'last_message_time' => $this->faker->dateTimeBetween('-1 month', 'now'),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                $numMessages = $this->faker->numberBetween(1, 8);
                for ($m = 0; $m < $numMessages; $m++) {
                    $senderType = $this->faker->randomElement(['customer', 'provider']);
                    $senderId   = ($senderType === 'customer') ? $customerId : $providerId;

                    DB::table('messages')->insert([
                        'message_id'      => (string) Str::uuid(),
                        'conversation_id' => $conversationId,
                        'sender_id'       => $senderId,
                        'sender_type'     => $senderType,
                        'message'         => $this->realisticMessage($senderType),
                        'is_read'         => $this->faker->boolean(70),
                        'created_at'      => $this->faker->dateTimeBetween('-1 month', 'now'),
                        'updated_at'      => now(),
                    ]);
                }

                $pairs[]              = $pairKey;
                $this->conversations[] = $conversationId;
            }
        }

        // Additional random conversations
        for ($i = 0; $i < 5; $i++) {
            $customerId     = $this->faker->randomElement($this->customers);
            $providerUserId = $this->faker->randomElement($this->providers);
            $providerId     = $this->providerProfiles[$providerUserId];
            $pairKey        = $customerId . '|' . $providerId;
            if (in_array($pairKey, $pairs)) continue;

            $conversationId = (string) Str::uuid();
            DB::table('conversations')->insert([
                'conversation_id'   => $conversationId,
                'user_id'           => $customerId,
                'provider_id'       => $providerId,
                'last_message_time' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            if ($this->faker->boolean(70)) {
                DB::table('messages')->insert([
                    'message_id'      => (string) Str::uuid(),
                    'conversation_id' => $conversationId,
                    'sender_id'       => $customerId,
                    'sender_type'     => 'customer',
                    'message'         => $this->realisticMessage('customer'),
                    'is_read'         => false,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            $pairs[] = $pairKey;
        }
    }

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

                $status      = $this->faker->randomElement(['SCHEDULED', 'PAID', 'FAILED']);
                $paidAt      = ($status === 'PAID') ? $this->faker->dateTimeBetween('-1 month', 'now') : null;
                $scheduledAt = ($status === 'SCHEDULED') ? $this->faker->dateTimeBetween('now', '+1 month') : null;

                DB::table('payouts')->insert([
                    'payout_id'    => (string) Str::uuid(),
                    'provider_id'  => $providerId,
                    'amount'       => $amount,
                    'currency'     => 'ZAR',
                    'status'       => $status,
                    'scheduled_at' => $scheduledAt,
                    'paid_at'      => $paidAt,
                    'reference'    => 'PO-' . strtoupper(Str::random(8)),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }

    protected function seedDashboardSummaries()
    {
        $allUsers = array_merge($this->customers, $this->providers);

        foreach ($allUsers as $userId) {
            $isProvider          = in_array($userId, $this->providers);
            $bookingsRequested   = 0;
            $bookingsOffered     = 0;
            $bookingsAccepted    = 0;
            $bookingsInProgress  = 0;
            $avgRating           = 0;

            if ($isProvider) {
                $providerId         = $this->providerProfiles[$userId];
                $bookingsOffered    = DB::table('service_requests')->where('provider_id', $providerId)->count();
                $bookingsAccepted   = DB::table('service_requests')->where('provider_id', $providerId)->whereIn('status', ['confirmed', 'in_progress', 'completed'])->count();
                $bookingsInProgress = DB::table('service_requests')->where('provider_id', $providerId)->where('status', 'in_progress')->count();
                $avgRating          = DB::table('service_reviews')->where('to_user_id', $userId)->avg('rating') ?? 0;
            } else {
                $bookingsRequested  = DB::table('service_requests')->where('user_id', $userId)->count();
                $bookingsAccepted   = DB::table('service_requests')->where('user_id', $userId)->whereIn('status', ['confirmed', 'in_progress', 'completed'])->count();
                $bookingsInProgress = DB::table('service_requests')->where('user_id', $userId)->where('status', 'in_progress')->count();
            }

            $unreadMessages = DB::table('messages')
                ->join('conversations', 'messages.conversation_id', '=', 'conversations.conversation_id')
                ->where(function ($q) use ($userId) {
                    $q->where('conversations.user_id', $userId)
                      ->orWhere('conversations.provider_id', $userId);
                })
                ->where('messages.is_read', false)
                ->where('messages.sender_id', '!=', $userId)
                ->count();

            $lastActivity = DB::table('users')->where('user_id', $userId)->value('updated_at');

            DB::table('user_dashboard_summaries')->insert([
                'user_id'             => $userId,
                'name'                => DB::table('users')->where('user_id', $userId)->value('full_name') . ' Summary',
                'bookings_requested'  => $bookingsRequested,
                'bookings_offered'    => $bookingsOffered,
                'bookings_accepted'   => $bookingsAccepted,
                'bookings_in_progress'=> $bookingsInProgress,
                'unread_messages'     => $unreadMessages,
                'average_rating'      => $avgRating,
                'last_activity_at'    => $lastActivity,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }
}