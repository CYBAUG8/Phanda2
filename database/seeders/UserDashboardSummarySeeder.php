<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserDashboardSummary;

class UserDashboardSummarySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        if (!$user) {
            $this->command->warn('No users found. Run UserSeeder first.');
            return;
        }

        UserDashboardSummary::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'bookings_requested' => 2,
                'bookings_offered' => 1,
                'bookings_accepted' => 3,
                'bookings_in_progress' => 1,
                'unread_messages' => 4,
                'average_rating' => 4.0,
                'last_activity_at' => now()->subMinutes(5),
            ]
        );
    }
}
