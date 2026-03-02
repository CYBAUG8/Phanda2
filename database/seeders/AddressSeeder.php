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
        $user = User::where('email', 'test@example.com')->first();

        if (!$user) {
            $this->command->warn('User not found. Run UserSeeder first.');
            return;
        }

        DB::table('addresses')->insert([
            'address_id' => Str::uuid(),
            'user_id' => $user->user_id, // ✅ dynamic UUID

            'type' => 'home',
            'street' => '123 Main Street',
            'city' => 'Johannesburg',
            'province' => 'Gauteng',
            'postal_code' => '2000',
            'country' => 'south_africa',
            'is_default' => true,

            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}