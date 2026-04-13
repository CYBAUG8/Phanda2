<?php

namespace Database\Seeders;
<<<<<<< HEAD
=======
use App\Models\Category;
>>>>>>> feature2
use App\Models\ProviderProfile;
use App\Models\Service;
use Illuminate\Database\Seeder;
<<<<<<< HEAD
use Illuminate\Support\Str;
=======
>>>>>>> feature2

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
<<<<<<< HEAD
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
=======
        $providers = ProviderProfile::query()
            ->with('user')
            ->orderBy('business_name')
            ->get();

        if ($providers->isEmpty()) {
            $this->command?->warn('No provider profiles found. Run ProviderProfileSeeder first.');
            return;
        }

        $categoryMap = Category::query()->pluck('id', 'slug');

        $templatesByCategory = [
            'cleaning' => [
                [
                    'title' => 'Deep Home Cleaning',
                    'description' => 'Full deep cleaning for kitchens, bathrooms, bedrooms, and living areas.',
                    'base_price' => 450,
                    'min_duration' => 180,
                ],
                [
                    'title' => 'Weekly Home Maintenance',
                    'description' => 'Routine dusting, mopping, vacuuming, and bathroom sanitation.',
                    'base_price' => 280,
                    'min_duration' => 120,
                ],
            ],
            'plumbing' => [
                [
                    'title' => 'Emergency Leak Repair',
                    'description' => 'Fast leak detection and repair for taps, pipes, and joints.',
                    'base_price' => 650,
                    'min_duration' => 90,
                ],
                [
                    'title' => 'Geyser Service and Installation',
                    'description' => 'Geyser maintenance, part replacement, and new installations.',
                    'base_price' => 1200,
                    'min_duration' => 240,
                ],
            ],
            'electrical' => [
                [
                    'title' => 'Electrical Fault Finding',
                    'description' => 'Diagnosis and repair of wiring issues, tripping circuits, and outlets.',
                    'base_price' => 800,
                    'min_duration' => 120,
                ],
                [
                    'title' => 'Lighting and Socket Installation',
                    'description' => 'Installation and replacement of lights, switches, and power points.',
                    'base_price' => 550,
                    'min_duration' => 90,
                ],
            ],
            'painting' => [
                [
                    'title' => 'Interior Wall Painting',
                    'description' => 'Surface prep and high-quality paint application for interior rooms.',
                    'base_price' => 1400,
                    'min_duration' => 300,
                ],
                [
                    'title' => 'Exterior Touch-Up and Repaint',
                    'description' => 'Exterior wall touch-ups and complete repainting for homes.',
                    'base_price' => 2200,
                    'min_duration' => 420,
                ],
            ],
            'moving' => [
                [
                    'title' => 'Apartment Moving Service',
                    'description' => 'Loading, transport, and unloading for apartment and small house moves.',
                    'base_price' => 950,
                    'min_duration' => 240,
                ],
                [
                    'title' => 'Packing and Furniture Setup',
                    'description' => 'Careful packing, unpacking, and furniture reassembly service.',
                    'base_price' => 700,
                    'min_duration' => 180,
                ],
            ],
            'gardening' => [
                [
                    'title' => 'Lawn and Garden Care',
                    'description' => 'Routine mowing, edging, weeding, and garden bed maintenance.',
                    'base_price' => 380,
                    'min_duration' => 90,
                ],
                [
                    'title' => 'Tree Trimming and Cleanup',
                    'description' => 'Safe pruning and cleanup of overgrown branches and shrubs.',
                    'base_price' => 850,
                    'min_duration' => 180,
                ],
            ],
            'carpentry' => [
                [
                    'title' => 'Cabinet Repair and Fitting',
                    'description' => 'Repair, adjustment, and fitting of kitchen and bedroom cabinets.',
                    'base_price' => 780,
                    'min_duration' => 150,
                ],
                [
                    'title' => 'Custom Shelving Installation',
                    'description' => 'Measured, cut, and installed custom shelving for home spaces.',
                    'base_price' => 920,
                    'min_duration' => 180,
                ],
            ],
            'appliance-repair' => [
                [
                    'title' => 'Washing Machine Repair',
                    'description' => 'Diagnosis and repair for common washing machine faults.',
                    'base_price' => 550,
                    'min_duration' => 90,
                ],
                [
                    'title' => 'Fridge and Freezer Repair',
                    'description' => 'Repairs for cooling, thermostat, and compressor-related issues.',
                    'base_price' => 650,
                    'min_duration' => 120,
                ],
            ],
        ];

        $availableSlugs = array_values(array_filter(
            array_keys($templatesByCategory),
            fn (string $slug): bool => $categoryMap->has($slug)
        ));

        if (empty($availableSlugs)) {
            $this->command?->warn('No matching categories found for provider service seeding.');
            return;
        }
>>>>>>> feature2

        foreach ($providers as $providerIndex => $providerProfile) {
            $providerName = trim((string) ($providerProfile->business_name ?: $providerProfile->user?->full_name ?: 'Provider'));
            $location = trim((string) ($providerProfile->service_area ?: 'Johannesburg'));

<<<<<<< HEAD
            $categorySlug = $serviceData['category'];
            unset($serviceData['category']);

            $serviceData['category_id'] = $categoryMap[$categorySlug];
=======
            $primarySlug = $availableSlugs[$providerIndex % count($availableSlugs)];
            $secondarySlug = $availableSlugs[($providerIndex + 3) % count($availableSlugs)];
            $assignedSlugs = array_unique([$primarySlug, $secondarySlug]);

            foreach ($assignedSlugs as $seedIndex => $slug) {
                $templates = $templatesByCategory[$slug];
                $template = $templates[($providerIndex + $seedIndex) % count($templates)];
>>>>>>> feature2

                Service::updateOrCreate(
                    [
                        'provider_id' => $providerProfile->provider_id,
                        'title' => $template['title'],
                    ],
                    [
                        'category_id' => $categoryMap->get($slug),
                        'provider_name' => $providerName,
                        'description' => $template['description'],
                        'base_price' => $template['base_price'],
                        'min_duration' => $template['min_duration'],
                        'location' => $location,
                        'rating' => 0,
                        'reviews_count' => 0,
                        'image' => null,
                        'is_active' => true,
                    ]
                );
            }
<<<<<<< HEAD

            Service::create($serviceData);
=======
>>>>>>> feature2
        }
    }
}
