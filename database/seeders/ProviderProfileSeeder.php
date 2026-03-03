<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ProviderProfile;
use Illuminate\Support\Str;

class ProviderProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Get only users with role = provider
        $providers = User::where('role', 'provider')->get();

        foreach ($providers as $provider) {

            ProviderProfile::updateOrCreate(
                ['user_id' => $provider->user_id], // prevent duplicates
                [
                    'provider_id' => Str::uuid(),
                    'user_id' => $provider->user_id,
                    'business_name' => $provider->full_name,
                    'bio' => 'Professional ' . $provider->full_name . ' offering quality services.',
                    'years_experience' => rand(1, 15),
                    'service_area' => 'Johannesburg',
                    'kyc_status' => 'APPROVED',
                    'is_online' => rand(0, 1),
                    'service_radius_km' => rand(5, 50),
                    'last_lat' => -26.2041,   // Johannesburg sample coords
                    'last_lng' => 28.0473,
                    'rating_avg' => rand(30, 50) / 10, // 3.0 - 5.0
                ]
            );
        }
    }
}