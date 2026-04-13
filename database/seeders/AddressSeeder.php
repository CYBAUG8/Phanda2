<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Run UserSeeder first.');
            return;
        }

        foreach ($users as $user) {

            DB::table('addresses')->insert([
                'address_id' => Str::uuid(),
                'user_id' => $user->user_id,

                'type' => 'home',
                'street' => fake()->streetAddress(),
                'city' => 'Johannesburg',
                'province' => 'Gauteng',
                'postal_code' => fake()->postcode(),
                'country' => 'south_africa',

                'is_default' => true,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Addresses created for all users.');
    }
}