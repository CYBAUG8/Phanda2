<?php

namespace Database\Seeders;
use App\Models\ProviderProfile;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // Cleaning (category_id will be looked up)
            [
                'category' => 'cleaning',
                'provider_name' => 'General Cleaning Service',
                'title' => 'Deep House Cleaning',
                'description' => 'Comprehensive deep cleaning of your entire home including kitchen, bathrooms, bedrooms, and living areas. We bring all cleaning supplies and equipment.',
                'base_price' => 450.00,
                'min_duration' => 180,
                'location' => 'Johannesburg, Sandton',
                'rating' => 4.8,
                'reviews_count' => 127,
            ],
            [
                'category' => 'cleaning',
                'provider_name' => 'General Cleaning Service',
                'title' => 'Regular Home Maintenance',
                'description' => 'Weekly or bi-weekly cleaning service to keep your home spotless. Includes dusting, vacuuming, mopping, and bathroom cleaning.',
                'base_price' => 280.00,
                'min_duration' => 120,
                'location' => 'Alexandra',
                'rating' => 4.6,
                'reviews_count' => 89,
            ],
            // Plumbing
            [
                'category' => 'plumbing',
                'provider_name' => 'Aaron Plumbing Services',
                'title' => 'Emergency Leak Repair',
                'description' => 'Fast response for burst pipes, leaking taps, and water damage. Available 7 days a week with same-day service.',
                'base_price' => 650.00,
                'min_duration' => 90,
                'location' => 'Alexandra',
                'rating' => 4.9,
                'reviews_count' => 203,
            ],
            [
                'category' => 'plumbing',
                'provider_name' => 'Aaron Plumbing Services',
                'title' => 'Geyser Installation & Repair',
                'description' => 'Professional geyser installation, repair, and maintenance. We work with all major brands and offer a warranty on all work.',
                'base_price' => 1200.00,
                'min_duration' => 240,
                'location' => 'Pretoria, Centurion',
                'rating' => 4.7,
                'reviews_count' => 56,
            ],
            // Electrical
            [
                'category' => 'electrical',
                'provider_name' => 'Electrical Repair Services',
                'title' => 'Electrical Wiring & Repairs',
                'description' => 'COC-compliant electrical wiring, fault finding, DB board upgrades, and light installations. Fully licensed electricians.',
                'base_price' => 800.00,
                'min_duration' => 120,
                'location' => 'Johannesburg, Midrand',
                'rating' => 4.9,
                'reviews_count' => 145,
            ],
            [
                'category' => 'electrical',
                'provider_name' => 'Electrical Repair Services',
                'title' => 'Solar Panel Installation',
                'description' => 'Beat load shedding with professional solar panel and inverter installation. Free assessment and quote included.',
                'base_price' => 2500.00,
                'min_duration' => 480,
                'location' => 'Durban, Umhlanga',
                'rating' => 4.5,
                'reviews_count' => 34,
            ],
            // Appliance Repair
            [
                'category' => 'appliance-repair',
                'provider_name' => 'Electrical Repair Services',
                'title' => 'Washing Machine Repair',
                'description' => 'Expert repair for all washing machine brands. Diagnosis, parts replacement, and testing included. 90-day warranty on repairs.',
                'base_price' => 550.00,
                'min_duration' => 90,
                'location' => 'Durban, Berea',
                'rating' => 4.7,
                'reviews_count' => 114,
            ],
            [
                'category' => 'appliance-repair',
                'provider_name' => 'Electrical Repair Services',
                'title' => 'Fridge & Freezer Repair',
                'description' => 'Professional refrigerator and freezer repairs. Gas refills, compressor repairs, and thermostat replacements.',
                'base_price' => 650.00,
                'min_duration' => 120,
                'location' => 'Cape Town, Bellville',
                'rating' => 4.5,
                'reviews_count' => 72,
            ],
        ];

        // Look up category IDs by slug
        $categoryMap = Category::pluck('id', 'slug')->toArray();
        $providerMap = ProviderProfile::pluck('provider_id', 'business_name')->toArray();

        foreach ($services as $serviceData) {

            $categorySlug = $serviceData['category'];
            unset($serviceData['category']);

            $serviceData['category_id'] = $categoryMap[$categorySlug];

            $serviceData['provider_id'] =
                $providerMap[$serviceData['provider_name']] ?? null;

            if (!$serviceData['provider_id']) {
                throw new \Exception("Provider not found: " . $serviceData['provider_name']);
            }

            Service::create($serviceData);
        }
    }
}