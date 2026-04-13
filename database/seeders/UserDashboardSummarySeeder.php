<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserDashboardSummary;

class UserDashboardSummarySeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Run UserSeeder first.');
            return;
        }

        foreach ($customers as $customer) {
            UserDashboardSummary::updateOrCreate(
                ['user_id' => $customer->user_id],
                [
                    'name' => $customer->full_name,
                    'bookings_requested'   => rand(1, 5),
                    'bookings_offered'     => rand(0, 3),
                    'bookings_accepted'    => rand(1, 5),
                    'bookings_in_progress' => rand(0, 2),
                    'unread_messages'      => rand(0, 6),
                    'average_rating'       => rand(30, 50) / 10, // 3.0 - 5.0
                    'last_activity_at'     => now()->subMinutes(rand(1, 120)),
                ]
            );
        }

        $this->command->info('Customer dashboard summaries seeded successfully.');
    }
}
