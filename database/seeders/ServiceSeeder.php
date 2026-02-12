<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // Cleaning (category_id will be looked up)
            [
                'category' => 'cleaning',
                'provider_name' => 'Spotless Home SA',
                'title' => 'Deep House Cleaning',
                'description' => 'Comprehensive deep cleaning of your entire home including kitchen, bathrooms, bedrooms, and living areas. We bring all cleaning supplies and equipment.',
                'price' => 450.00,
                'duration_minutes' => 180,
                'location' => 'Johannesburg, Sandton',
                'rating' => 4.8,
                'reviews_count' => 127,
            ],
            [
                'category' => 'cleaning',
                'provider_name' => 'Mama Nkosi Cleaners',
                'title' => 'Regular Home Maintenance',
                'description' => 'Weekly or bi-weekly cleaning service to keep your home spotless. Includes dusting, vacuuming, mopping, and bathroom cleaning.',
                'price' => 280.00,
                'duration_minutes' => 120,
                'location' => 'Johannesburg, Soweto',
                'rating' => 4.6,
                'reviews_count' => 89,
            ],
            // Plumbing
            [
                'category' => 'plumbing',
                'provider_name' => 'PipeFix Pro',
                'title' => 'Emergency Leak Repair',
                'description' => 'Fast response for burst pipes, leaking taps, and water damage. Available 7 days a week with same-day service.',
                'price' => 650.00,
                'duration_minutes' => 90,
                'location' => 'Cape Town, Southern Suburbs',
                'rating' => 4.9,
                'reviews_count' => 203,
            ],
            [
                'category' => 'plumbing',
                'provider_name' => 'Drain Masters',
                'title' => 'Geyser Installation & Repair',
                'description' => 'Professional geyser installation, repair, and maintenance. We work with all major brands and offer a warranty on all work.',
                'price' => 1200.00,
                'duration_minutes' => 240,
                'location' => 'Pretoria, Centurion',
                'rating' => 4.7,
                'reviews_count' => 56,
            ],
            // Electrical
            [
                'category' => 'electrical',
                'provider_name' => 'SparkPro Electrical',
                'title' => 'Electrical Wiring & Repairs',
                'description' => 'COC-compliant electrical wiring, fault finding, DB board upgrades, and light installations. Fully licensed electricians.',
                'price' => 800.00,
                'duration_minutes' => 120,
                'location' => 'Johannesburg, Midrand',
                'rating' => 4.9,
                'reviews_count' => 145,
            ],
            [
                'category' => 'electrical',
                'provider_name' => 'PowerUp Solutions',
                'title' => 'Solar Panel Installation',
                'description' => 'Beat load shedding with professional solar panel and inverter installation. Free assessment and quote included.',
                'price' => 2500.00,
                'duration_minutes' => 480,
                'location' => 'Durban, Umhlanga',
                'rating' => 4.5,
                'reviews_count' => 34,
            ],
            // Painting
            [
                'category' => 'painting',
                'provider_name' => 'Fresh Coat Painters',
                'title' => 'Interior Room Painting',
                'description' => 'Professional interior painting with premium paints. Includes wall preparation, priming, two coats, and clean-up. Per room pricing.',
                'price' => 1500.00,
                'duration_minutes' => 360,
                'location' => 'Johannesburg, Rosebank',
                'rating' => 4.7,
                'reviews_count' => 78,
            ],
            [
                'category' => 'painting',
                'provider_name' => 'Rainbow Finishes',
                'title' => 'Exterior House Painting',
                'description' => 'Weather-resistant exterior painting including power washing, crack filling, and premium weather-guard paint application.',
                'price' => 2200.00,
                'duration_minutes' => 480,
                'location' => 'Cape Town, Constantia',
                'rating' => 4.4,
                'reviews_count' => 41,
            ],
            // Moving
            [
                'category' => 'moving',
                'provider_name' => 'QuickMove SA',
                'title' => 'Full House Moving',
                'description' => 'Complete moving service including packing, loading, transport, and unpacking. Furniture blankets and insurance included.',
                'price' => 1800.00,
                'duration_minutes' => 360,
                'location' => 'Johannesburg, Fourways',
                'rating' => 4.6,
                'reviews_count' => 92,
            ],
            // Gardening
            [
                'category' => 'gardening',
                'provider_name' => 'Green Thumb Gardens',
                'title' => 'Garden Maintenance Package',
                'description' => 'Monthly garden maintenance including lawn mowing, hedge trimming, weeding, and general garden clean-up.',
                'price' => 350.00,
                'duration_minutes' => 180,
                'location' => 'Pretoria, Waterkloof',
                'rating' => 4.8,
                'reviews_count' => 167,
            ],
            [
                'category' => 'gardening',
                'provider_name' => 'LawnKing Services',
                'title' => 'Landscaping & Design',
                'description' => 'Transform your outdoor space with professional landscaping. Includes design consultation, planting, irrigation, and paving.',
                'price' => 2000.00,
                'duration_minutes' => 480,
                'location' => 'Johannesburg, Bryanston',
                'rating' => 4.3,
                'reviews_count' => 28,
            ],
            // Carpentry
            [
                'category' => 'carpentry',
                'provider_name' => 'WoodCraft Studio',
                'title' => 'Custom Built-In Cupboards',
                'description' => 'Bespoke built-in cupboards and wardrobes tailored to your space. Choice of materials and finishes available.',
                'price' => 1800.00,
                'duration_minutes' => 480,
                'location' => 'Johannesburg, Randburg',
                'rating' => 4.9,
                'reviews_count' => 63,
            ],
            // Appliance Repair
            [
                'category' => 'appliance-repair',
                'provider_name' => 'FixIt Appliance Repairs',
                'title' => 'Washing Machine Repair',
                'description' => 'Expert repair for all washing machine brands. Diagnosis, parts replacement, and testing included. 90-day warranty on repairs.',
                'price' => 550.00,
                'duration_minutes' => 90,
                'location' => 'Durban, Berea',
                'rating' => 4.7,
                'reviews_count' => 114,
            ],
            [
                'category' => 'appliance-repair',
                'provider_name' => 'CoolTech Repairs',
                'title' => 'Fridge & Freezer Repair',
                'description' => 'Professional refrigerator and freezer repairs. Gas refills, compressor repairs, and thermostat replacements.',
                'price' => 650.00,
                'duration_minutes' => 120,
                'location' => 'Cape Town, Bellville',
                'rating' => 4.5,
                'reviews_count' => 72,
            ],
        ];

        // Look up category IDs by slug
        $categoryMap = Category::pluck('id', 'slug')->toArray();

        foreach ($services as $serviceData) {
            $categorySlug = $serviceData['category'];
            unset($serviceData['category']);

            $serviceData['category_id'] = $categoryMap[$categorySlug];
            Service::create($serviceData);
        }
    }
}