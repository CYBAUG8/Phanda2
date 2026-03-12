<?php

namespace Database\Seeders;
use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
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

        foreach ($providers as $providerIndex => $providerProfile) {
            $providerName = trim((string) ($providerProfile->business_name ?: $providerProfile->user?->full_name ?: 'Provider'));
            $location = trim((string) ($providerProfile->service_area ?: 'Johannesburg'));

            $primarySlug = $availableSlugs[$providerIndex % count($availableSlugs)];
            $secondarySlug = $availableSlugs[($providerIndex + 3) % count($availableSlugs)];
            $assignedSlugs = array_unique([$primarySlug, $secondarySlug]);

            foreach ($assignedSlugs as $seedIndex => $slug) {
                $templates = $templatesByCategory[$slug];
                $template = $templates[($providerIndex + $seedIndex) % count($templates)];

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
        }
    }
}
