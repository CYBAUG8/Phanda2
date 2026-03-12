<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdditionalMarketplaceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->createAdditionalCustomer();
        $this->createProvidersAndServices();
    }

    private function createAdditionalCustomer(): void
    {
        $customer = User::withTrashed()->firstOrNew([
            'email' => 'new.customer@phanda.co.za',
        ]);

        if (!$customer->user_id) {
            $customer->user_id = (string) Str::uuid();
        }

        $customer->fill([
            'full_name' => 'New Customer',
            'phone' => '+27 71 555 1001',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
            'deleted_at' => null,
        ]);

        $customer->save();
    }

    private function createProvidersAndServices(): void
    {
        $categoryMap = Category::query()->pluck('id', 'slug');
        $fallbackCategoryId = Category::query()->value('id');

        if (!$fallbackCategoryId) {
            $this->command?->warn('No categories found. Skipping provider and service additions.');
            return;
        }

        $providerPayloads = [
            [
                'email' => 'sparkline.electrical@phanda.co.za',
                'full_name' => 'Sparkline Electrical',
                'phone' => '+27 71 555 2001',
                'business_name' => 'Sparkline Electrical Services',
                'service_area' => 'Johannesburg, Gauteng',
                'years_experience' => 8,
                'services' => [
                    [
                        'category_slug' => 'electrical',
                        'title' => 'DB Board Upgrade',
                        'description' => 'Safe and compliant DB board upgrade with testing and labeling.',
                        'base_price' => 1400.00,
                        'min_duration' => 180,
                    ],
                    [
                        'category_slug' => 'electrical',
                        'title' => 'Emergency Power Fault Repair',
                        'description' => 'Urgent fault diagnosis and repair for no-power and tripping issues.',
                        'base_price' => 900.00,
                        'min_duration' => 120,
                    ],
                ],
            ],
            [
                'email' => 'primeplumb.team@phanda.co.za',
                'full_name' => 'PrimePlumb Team',
                'phone' => '+27 71 555 2002',
                'business_name' => 'PrimePlumb Solutions',
                'service_area' => 'Pretoria, Gauteng',
                'years_experience' => 10,
                'services' => [
                    [
                        'category_slug' => 'plumbing',
                        'title' => 'Burst Pipe Repair',
                        'description' => 'Rapid burst pipe isolation and permanent repair service.',
                        'base_price' => 750.00,
                        'min_duration' => 90,
                    ],
                    [
                        'category_slug' => 'plumbing',
                        'title' => 'Geyser Maintenance Visit',
                        'description' => 'Inspection, valve checks, and preventive geyser maintenance.',
                        'base_price' => 680.00,
                        'min_duration' => 75,
                    ],
                ],
            ],
            [
                'email' => 'freshnest.cleaning@phanda.co.za',
                'full_name' => 'FreshNest Cleaning',
                'phone' => '+27 71 555 2003',
                'business_name' => 'FreshNest Home Cleaning',
                'service_area' => 'Cape Town, Western Cape',
                'years_experience' => 6,
                'services' => [
                    [
                        'category_slug' => 'cleaning',
                        'title' => 'Post-Renovation Cleaning',
                        'description' => 'Dust and debris cleanup after renovations with detailed finishing.',
                        'base_price' => 1100.00,
                        'min_duration' => 240,
                    ],
                    [
                        'category_slug' => 'cleaning',
                        'title' => 'Move-In Deep Cleaning',
                        'description' => 'Complete sanitizing and deep cleaning before moving in.',
                        'base_price' => 800.00,
                        'min_duration' => 180,
                    ],
                ],
            ],
        ];

        foreach ($providerPayloads as $payload) {
            $providerUser = User::withTrashed()->firstOrNew([
                'email' => $payload['email'],
            ]);

            if (!$providerUser->user_id) {
                $providerUser->user_id = (string) Str::uuid();
            }

            $providerUser->fill([
                'full_name' => $payload['full_name'],
                'phone' => $payload['phone'],
                'password' => Hash::make('password'),
                'role' => 'provider',
                'email_verified_at' => now(),
                'deleted_at' => null,
            ]);
            $providerUser->save();

            $providerProfile = ProviderProfile::withTrashed()->firstOrNew([
                'user_id' => $providerUser->user_id,
            ]);

            if (!$providerProfile->provider_id) {
                $providerProfile->provider_id = (string) Str::uuid();
            }

            $providerProfile->fill([
                'business_name' => $payload['business_name'],
                'bio' => 'Reliable team providing quality service with transparent pricing.',
                'years_experience' => $payload['years_experience'],
                'service_area' => $payload['service_area'],
                'kyc_status' => 'APPROVED',
                'is_online' => true,
                'service_radius_km' => 35,
                'rating_avg' => 0,
                'deleted_at' => null,
            ]);
            $providerProfile->save();

            foreach ($payload['services'] as $servicePayload) {
                $categoryId = $categoryMap->get($servicePayload['category_slug'], $fallbackCategoryId);

                $service = Service::withTrashed()->firstOrNew([
                    'provider_id' => $providerProfile->provider_id,
                    'title' => $servicePayload['title'],
                ]);

                $service->fill([
                    'category_id' => $categoryId,
                    'provider_name' => $providerProfile->business_name,
                    'description' => $servicePayload['description'],
                    'base_price' => $servicePayload['base_price'],
                    'min_duration' => $servicePayload['min_duration'],
                    'location' => $providerProfile->service_area ?? 'South Africa',
                    'rating' => 0,
                    'reviews_count' => 0,
                    'image' => null,
                    'is_active' => true,
                    'deleted_at' => null,
                ]);

                $service->save();
            }
        }
    }
}
