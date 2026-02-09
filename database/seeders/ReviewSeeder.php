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
        ];

        foreach ($service_reviews as $review) {
            Review::create($review);
        }
    }
}

