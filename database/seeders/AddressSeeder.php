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
        $user = User::where('role', 'customer')->first();

        if (!$user) {
            $this->command->warn('No customer found. Run UserSeeder first.');
            return;
        }

        DB::table('addresses')->insert([
            'address_id' => Str::uuid(),
            'user_id' => $user->user_id,

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