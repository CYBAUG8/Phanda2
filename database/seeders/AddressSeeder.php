<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('addresses')->insert([
            'address_id' => Str::uuid(),
            'user_id' => '3f405662-e611-4eec-b510-17b3f56b5b22',

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
