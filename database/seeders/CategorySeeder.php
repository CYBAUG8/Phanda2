<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cleaning',
                'slug' => 'cleaning',
                'icon' => 'fa-broom',
                'description' => 'Home and office cleaning services including deep cleaning, regular maintenance, and move-in/move-out cleaning.',
            ],
            [
                'name' => 'Plumbing',
                'slug' => 'plumbing',
                'icon' => 'fa-wrench',
                'description' => 'Professional plumbing services for repairs, installations, and maintenance.',
            ],
            [
                'name' => 'Electrical',
                'slug' => 'electrical',
                'icon' => 'fa-bolt',
                'description' => 'Licensed electrical work including wiring, installations, and fault finding.',
            ],
            [
                'name' => 'Painting',
                'slug' => 'painting',
                'icon' => 'fa-paint-roller',
                'description' => 'Interior and exterior painting, wall preparation, and colour consultation.',
            ],
            [
                'name' => 'Moving',
                'slug' => 'moving',
                'icon' => 'fa-truck',
                'description' => 'Household and office moving, packing, and furniture assembly services.',
            ],
            [
                'name' => 'Gardening',
                'slug' => 'gardening',
                'icon' => 'fa-leaf',
                'description' => 'Garden maintenance, landscaping, tree trimming, and lawn care.',
            ],
            [
                'name' => 'Carpentry',
                'slug' => 'carpentry',
                'icon' => 'fa-hammer',
                'description' => 'Custom woodwork, furniture repair, cabinet making, and installations.',
            ],
            [
                'name' => 'Appliance Repair',
                'slug' => 'appliance-repair',
                'icon' => 'fa-tools',
                'description' => 'Repair and maintenance for household appliances including washing machines, fridges, and stoves.',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
