<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Review;
use Illuminate\Support\Str;

class ReviewSeeder extends Seeder {
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $providers = User::where('role', 'provider')->get();

        foreach ($customers as $customer) {
            foreach ($providers->random(2) as $provider) {

                Review::updateOrCreate(
                    [
                        'from_user_id' => $customer->user_id,
                        'to_user_id'   => $provider->user_id,
                    ],
                    [
                        'review_id'         => Str::uuid(),
                        'service_id' => 'General Service',
                        'rating'     => rand(3,5),
                        'comment'    => 'Great service from ' . $provider->full_name,
                    ]
                );
            }
        }
    }
}
