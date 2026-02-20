<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use Illuminate\Support\Str;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $service_reviews = [
            [
                'id' => Str::uuid(),
                'service_id' => 'General Cleaning',
                'user_id' => '2',
                'provider_id' => 'cleaner-001',
                'rating' => 5,
                'comment' => 'Amazing service!',
                
            ],
            [
                'id' => Str::uuid(),
                'service_id' => 'Plumbing Repair',
                'user_id' => '3',
                'provider_id' => 'plumber-001',
                'rating' => 4,
                'comment' => 'Good, reliable plumber.',
            ],
            [
                'id' => Str::uuid(),
                'service_id' => 'General Cleaning',
                'provider_id' => 'cleaner-001',
                'user_id' => '4',
                'rating' => 5,
                'comment' => 'Excellent cleaning and on time!',
            ],
            [
                'id' => Str::uuid(),
                'service_id' => 'General Cleaning',
                'provider_id' => 'cleaner-001',
                'user_id' => '5',
                'rating' => 3,
                'comment' => 'It was okay, could be more thorough.',
            ],
            [
                'id' => Str::uuid(),
                'service_id' => 'General Cleaning',
                'provider_id' => 'cleaner-001',
                'user_id' => '6',
                'rating' => 5,
                'comment' => 'Loved the attention to detail!',
            ],
            [
                'id' => Str::uuid(),
                'service_id' => 'Electrical Repair',
                'provider_id' => 'electrical-001',
                'user_id' => '7',
                'rating' => 5,
                'comment' => 'Job well done, fixed my issue quickly.',
            ],
            [
                'id' => Str::uuid(),
                'service_id' => 'Bantu Construction',
                'provider_id' => 'construction-001',
                'user_id' => '7',
                'rating' => 5,
                'comment' => 'Great craftsmanship and professionalism.',
            ],
            [
                'id' => Str::uuid(),
                'service_id' => 'Bantu Construction',
                'provider_id' => 'construction-001',
                'user_id' => '2',
                'rating' => 5,
                'comment' => 'Great job.',
            ],
             [
                'id' => Str::uuid(),
                'service_id' => 'Catering Service',
                'provider_id' => 'catering-001',
                'user_id' => '8',
                'rating' => 4,
                'comment' => 'Delicious food and great service.',
            ],
             [
                'id' => Str::uuid(),
                'service_id' => 'Catering Service',
                'provider_id' => 'catering-001',
                'user_id' => '9',
                'rating' => 5,
                'comment' => 'The best caterer I have ever used!',
            ],
        ];

        foreach ($service_reviews as $review) {
            Review::create($review);
        }
    }
}

